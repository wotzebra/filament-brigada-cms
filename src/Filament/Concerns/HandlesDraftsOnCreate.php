<?php

namespace Wotz\FilamentBrigadaCms\Filament\Concerns;

use Wotz\FilamentBrigadaCms\Filament\Actions\SaveDraftAction;
use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Model;

trait HandlesDraftsOnCreate
{
    protected bool $shouldSaveAsDraft = true;

    /**
     * The draft actions in their intended order, for the head of a create page. The
     * counterpart to `HandlesDraftsOnEdit::getDraftHeaderActions()`:
     *
     *     protected function getHeaderActions(): array
     *     {
     *         return [
     *             ...$this->getDraftHeaderActions(),
     *             $this->getCancelFormAction(),
     *         ];
     *     }
     *
     * Live preview is included when the page also uses `HasLivePreviewComponent`.
     *
     * @return array<int, Action>
     */
    protected function getDraftHeaderActions(): array
    {
        return array_values(array_filter([
            /*
             * Greyed deliberately: previewing is never the primary action on this page —
             * saving is. `wotz/filament-live-preview` builds the group as `primary`, so
             * without this a page shows two competing primary buttons.
             */
            method_exists($this, 'getLivePreviewAction') ? $this->getLivePreviewAction()->color('gray') : null,
            $this->getCreateFormAction()->submit(null)->action('create'),
            $this->getCreateAnotherFormAction(),
        ]));
    }

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
