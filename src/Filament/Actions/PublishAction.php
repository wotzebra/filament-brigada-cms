<?php

namespace Wotz\FilamentBrigadaCms\Filament\Actions;

use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class PublishAction extends Action
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('filament-brigada-cms::cms.drafts.publish'))
            ->icon(Heroicon::GlobeAlt)
            ->color('success')
            ->action(function (): void {
                $livewire = $this->getLivewire();

                if (! $livewire instanceof EditRecord) {
                    return;
                }

                $livewire->save();

                $record = $livewire->getRecord()->fresh();
                $record->withoutRevision()->publish()->save();

                $currentRecord = $record->revisions()->current()->first() ?? $record;

                $livewire->redirect($livewire::getResource()::getUrl('edit', ['record' => $currentRecord]));
            });
    }
}
