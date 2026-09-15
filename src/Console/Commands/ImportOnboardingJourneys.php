<?php

namespace Wotz\FilamentBrigadaCms\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use SplFileInfo;
use Wallacemartinss\FilamentOnboarding\Models\OnboardingCondition;
use Wallacemartinss\FilamentOnboarding\Models\OnboardingFlow;
use Wallacemartinss\FilamentOnboarding\Models\OnboardingStep;

/**
 * Apply the journeys this project has to the current environment. Runs on
 * deploy, and is safe to run again: everything is addressed by its natural key
 * — a flow by `key`, a step by its flow and `key`, a condition by `key` — so a
 * second run converges rather than duplicating.
 *
 * Two sources, in order. The journeys this package ships describe the panel
 * every Brigada CMS has: pages, media, menus, redirects. Then the project's own
 * `database/onboarding/`, which adds journeys about the things only it has —
 * and, sharing a key, replaces a shipped one outright. So a project can rewrite
 * "Publish a page" for its own vocabulary without forking the package, and a
 * project that says nothing still gets the four.
 *
 * What it will not touch is progress. `onboarding_flow_progress`,
 * `onboarding_step_progress` and `onboarding_preferences` are what people have
 * actually done, they are per-environment by nature, and a deploy that resets
 * them would re-onboard everybody.
 */
#[Signature('onboarding:import {--prune : Remove flows and steps that are no longer in the fixtures} {--dry-run : Report what would change without writing} {--app-only : Skip the journeys this package ships}')]
#[Description('Import onboarding journeys into this environment')]
class ImportOnboardingJourneys extends Command
{
    public function handle(): int
    {
        $files = $this->fixtures();

        if ($files->isEmpty()) {
            $this->components->warn('No journey fixtures found.');

            return self::SUCCESS;
        }

        $seen = [];

        DB::transaction(function () use ($files, &$seen): void {
            foreach ($files as $file) {
                $data = json_decode($file->getContents(), true);

                if (! is_array($data)) {
                    $this->components->error("{$file->getFilename()} is not valid JSON — skipped.");

                    continue;
                }

                if ($file->getFilenameWithoutExtension() === 'conditions') {
                    $this->importConditions($data);

                    continue;
                }

                $seen[] = $this->importFlow($data);
            }

            if ($this->option('prune')) {
                $this->prune($seen);
            }
        }, attempts: 1);

        if ($this->option('dry-run')) {
            DB::rollBack();
        }

        return self::SUCCESS;
    }

    /**
     * The fixtures to apply, shipped first and the project's own last.
     *
     * Keyed on filename so a project file replaces the shipped one it shares a
     * name with, rather than the two fighting over the same flow key.
     *
     * @return Collection<string, SplFileInfo>
     */
    private function fixtures(): Collection
    {
        $directories = [
            ...($this->option('app-only') ? [] : [__DIR__ . '/../../../resources/onboarding']),
            base_path(ExportOnboardingJourneys::DIRECTORY),
        ];

        return collect($directories)
            ->filter(fn (string $directory): bool => File::isDirectory($directory))
            ->flatMap(fn (string $directory): array => File::files($directory))
            ->filter(fn (SplFileInfo $file): bool => $file->getExtension() === 'json')
            ->keyBy(fn (SplFileInfo $file): string => $file->getFilename())
            ->sortKeys();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function importFlow(array $data): string
    {
        $steps = $data['steps'] ?? [];
        unset($data['steps']);

        $flow = OnboardingFlow::updateOrCreate(['key' => $data['key']], $data);

        $keys = [];

        foreach ($steps as $step) {
            OnboardingStep::updateOrCreate(
                ['flow_id' => $flow->id, 'key' => $step['key']],
                $step,
            );

            $keys[] = $step['key'];
        }

        $orphans = $flow->steps()->whereNotIn('key', $keys)->get();

        foreach ($orphans as $orphan) {
            $orphan->delete();
        }

        $this->components->twoColumnDetail(
            "flow <fg=cyan>{$flow->key}</>",
            count($keys) . ' step' . (count($keys) === 1 ? '' : 's')
                . ($orphans->isNotEmpty() ? ", {$orphans->count()} removed" : ''),
        );

        return $flow->key;
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    private function importConditions(array $rows): void
    {
        foreach ($rows as $row) {
            OnboardingCondition::updateOrCreate(['key' => $row['key']], $row);
        }

        $this->components->twoColumnDetail('conditions', count($rows) . ' applied');
    }

    /**
     * @param  list<string>  $seen
     */
    private function prune(array $seen): void
    {
        $stale = OnboardingFlow::query()->whereNotIn('key', $seen)->get();

        foreach ($stale as $flow) {
            $this->components->twoColumnDetail("flow <fg=yellow>{$flow->key}</>", '<fg=yellow>pruned</>');
            $flow->delete();
        }
    }
}
