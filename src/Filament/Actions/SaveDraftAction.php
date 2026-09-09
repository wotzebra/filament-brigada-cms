<?php

namespace Wotz\FilamentBrigadaCms\Filament\Actions;

use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;

class SaveDraftAction extends Action
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('filament-brigada-cms::cms.drafts.save_draft'))
            ->icon(Heroicon::DocumentText)
            ->color('gray')
            ->action(function (): void {
                $livewire = $this->getLivewire();

                if (! $livewire instanceof EditRecord) {
                    return;
                }

                $livewire->enableDraftSave();
                $livewire->save();
                $livewire->disableDraftSave();
            });
    }

    public function handleRecordUpdate(EditRecord $livewire, Model $record, array $data): ?Model
    {
        if (! $livewire->shouldSaveAsDraft()) {
            return null;
        }

        $record->updateAsDraft($data);

        $current = $record->revisions()->current()->first();

        if ($current) {
            $livewire->redirect($livewire::getResource()::getUrl('edit', ['record' => $current]));
        }

        return $record;
    }
}
