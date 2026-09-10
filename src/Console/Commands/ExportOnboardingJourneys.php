<?php

namespace Wotz\FilamentBrigadaCms\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Wallacemartinss\FilamentOnboarding\Models\OnboardingCondition;
use Wallacemartinss\FilamentOnboarding\Models\OnboardingFlow;

/**
 * Journeys are written in the panel and live as database rows, which means that
 * without this they exist on exactly one machine. Export writes them to
 * `database/onboarding/`, where they are reviewed in a pull request and applied
 * on deploy like any other change — so a journey travels dev to production the
 * way code does, not the way a database dump does.
 *
 * Only the authored content travels. The three progress tables are per-subject
 * state and belong to the environment they were earned in.
 */
#[Signature('onboarding:export {--flow=* : Export only these flow keys} {--dry-run : Show what would be written without writing}')]
#[Description('Export onboarding journeys from the database to database/onboarding/')]
class ExportOnboardingJourneys extends Command
{
    public const DIRECTORY = 'database/onboarding';

    /**
     * Columns that are the row's identity or its bookkeeping, not its content.
     *
     * @var list<string>
     */
    private const NOT_CONTENT = ['id', 'flow_id', 'created_at', 'updated_at'];

    public function handle(): int
    {
        $directory = base_path(self::DIRECTORY);

        $flows = OnboardingFlow::query()
            ->when($this->option('flow'), fn ($query, array $keys) => $query->whereIn('key', $keys))
            ->with('steps')
            ->orderBy('sort_order')
            ->orderBy('key')
            ->get();

        if ($flows->isEmpty()) {
            $this->components->warn('No journeys to export.');

            return self::SUCCESS;
        }

        $unportable = $this->unportableUrls($flows);

        if (filled($unportable)) {
            $this->components->error('A journey points at a hard-coded URL, which will not survive the trip to another environment:');

            foreach ($unportable as $line) {
                $this->line("  {$line}");
            }

            $this->components->info('Use a route instead — `cta_route`, or a stop\'s `route` — which is resolved per environment.');

            return self::FAILURE;
        }

        if (! $this->option('dry-run')) {
            File::ensureDirectoryExists($directory);
        }

        foreach ($flows as $flow) {
            $this->write("{$directory}/{$flow->key}.json", $this->flowToArray($flow));
        }

        $conditions = OnboardingCondition::query()->orderBy('key')->get();

        if ($conditions->isNotEmpty()) {
            $this->write(
                "{$directory}/conditions.json",
                $conditions->map(fn (OnboardingCondition $condition) => $this->rowToArray($condition))->all(),
            );
        }

        return self::SUCCESS;
    }

    /**
     * @return array<string, mixed>
     */
    private function flowToArray(OnboardingFlow $flow): array
    {
        return [
            ...$this->rowToArray($flow),
            'steps' => $flow->steps
                ->sortBy('sort_order')
                ->values()
                ->map(fn ($step) => $this->rowToArray($step))
                ->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function rowToArray(mixed $record): array
    {
        $attributes = collect($record->getAttributes())
            ->except(self::NOT_CONTENT)
            ->map(fn (mixed $value, string $key) => $record->{$key})
            ->all();

        ksort($attributes);

        return $attributes;
    }

    /**
     * An absolute URL names a host, and the host is the one thing that differs
     * between the environments this file is meant to cross.
     *
     * @param  Collection<int, OnboardingFlow>  $flows
     * @return list<string>
     */
    private function unportableUrls($flows): array
    {
        $found = [];

        foreach ($flows as $flow) {
            foreach ($flow->steps as $step) {
                foreach (['cta_url', 'visit_url'] as $column) {
                    if (filled($step->{$column})) {
                        $found[] = "{$flow->key}/{$step->key}: {$column} = {$step->{$column}}";
                    }
                }

                foreach ($step->tour_steps ?? [] as $index => $tourStep) {
                    if (filled($tourStep['url'] ?? null)) {
                        $found[] = "{$flow->key}/{$step->key}: stop " . ($index + 1) . " url = {$tourStep['url']}";
                    }
                }
            }
        }

        return $found;
    }

    /**
     * @param  array<mixed>  $data
     */
    private function write(string $path, array $data): void
    {
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL;

        if ($this->option('dry-run')) {
            $this->components->twoColumnDetail(str_replace(base_path() . '/', '', $path), strlen($json) . ' bytes');

            return;
        }

        File::put($path, $json);

        $this->components->twoColumnDetail(str_replace(base_path() . '/', '', $path), '<fg=green>written</>');
    }
}
