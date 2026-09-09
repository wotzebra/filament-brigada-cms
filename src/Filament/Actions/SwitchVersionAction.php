<?php

namespace Wotz\FilamentBrigadaCms\Filament\Actions;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;

class SwitchVersionAction extends ActionGroup
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('filament-brigada-cms::cms.drafts.version'))
            ->icon(Heroicon::Clock)
            ->button()
            ->dropdown()
            ->dropdownWidth(Width::ExtraSmall)
            ->outlined()
            ->color('gray');
    }

    public static function makeGroup(EditRecord $livewire): static
    {
        return static::make(static::getRevisionActions($livewire))
            ->badge(fn (): int => $livewire->getRecord()->revisions()->withDrafts()->count());
    }

    protected static function getRevisionActions(EditRecord $livewire): array
    {
        return $livewire->getRecord()
            ->revisions()
            ->withDrafts()
            ->latest($livewire->getRecord()->getPublishedAtColumn())
            ->latest('id')
            ->get()
            ->map(function (Model $revision) use ($livewire): Action {
                $label = sprintf(
                    '#%s %s (%s)',
                    $revision->getKey(),
                    $revision->created_at?->format('Y-m-d H:i'),
                    $revision->isPublished() ? 'published' : 'draft'
                );

                return Action::make("switchVersion_{$revision->getKey()}")
                    ->label($label)
                    ->disabled($livewire->getRecord()->is($revision))
                    ->action(
                        fn () => $livewire->redirect($livewire::getResource()::getUrl('edit', ['record' => $revision]))
                    );
            })
            ->all();
    }
}
