<?php

namespace Wotz\FilamentBrigadaCms\Filament\Concerns;

use Wotz\FilamentBrigadaCms\Filament\Actions\PublishAction;
use Wotz\FilamentBrigadaCms\Filament\Actions\SaveDraftAction;
use Wotz\FilamentBrigadaCms\Filament\Actions\SwitchVersionAction;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Illuminate\Database\Eloquent\Model;

trait HandlesDraftsOnEdit
{
    protected bool $shouldSaveAsDraft = false;

    protected function getSaveDraftAction(): Action
    {
        return SaveDraftAction::make('saveDraft')
            ->visible(fn (): bool => $this->getRecord()->isPublished())
            ->color(fn (): ?string => $this->getRecord()->isPublished() ? 'primary' : 'gray');
    }

    protected function getSaveFormAction(): Action
    {
        $action = parent::getSaveFormAction();

        if ($this->getRecord()->isPublished()) {
            $action->color('gray');
        }

        return $action;
    }

    protected function getPublishAction(): Action
    {
        return PublishAction::make('publish')
            ->visible(fn (): bool => ! $this->getRecord()->isPublished());
    }

    protected function getSwitchVersionAction(): Action|ActionGroup
    {
        return SwitchVersionAction::makeGroup($this);
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $draftRecord = SaveDraftAction::make('saveDraft')
            ->handleRecordUpdate($this, $record, $data);

        if ($draftRecord) {
            return $draftRecord;
        }

        $record->withoutRevision()->update($data);

        return $record;
    }

    public function enableDraftSave(): void
    {
        $this->shouldSaveAsDraft = true;
    }

    public function disableDraftSave(): void
    {
        $this->shouldSaveAsDraft = false;
    }

    public function shouldSaveAsDraft(): bool
    {
        return $this->shouldSaveAsDraft;
    }
}
