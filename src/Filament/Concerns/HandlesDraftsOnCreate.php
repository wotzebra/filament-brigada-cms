<?php

namespace Wotz\FilamentBrigadaCms\Filament\Concerns;

use Wotz\FilamentBrigadaCms\Filament\Actions\SaveDraftAction;
use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Model;

trait HandlesDraftsOnCreate
{
    protected bool $shouldSaveAsDraft = true;

    protected function getSaveDraftAction(): Action
    {
        return SaveDraftAction::make('saveDraft')
            ->action('createDraft');
    }

    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()
            ->label(__('filament-brigada-cms::cms.drafts.create_draft'));
    }

    public function createDraft(): void
    {
        $this->shouldSaveAsDraft = true;

        $this->create();

        $this->shouldSaveAsDraft = false;
    }

    protected function handleRecordCreation(array $data): Model
    {
        $record = new ($this->getModel())($data);

        if ($this->shouldSaveAsDraft) {
            $record->{$record->getIsPublishedColumn()} = false;
        }

        if ($parentRecord = $this->getParentRecord()) {
            return $this->associateRecordWithParent($record, $parentRecord);
        }

        $record->save();

        return $record;
    }
}
