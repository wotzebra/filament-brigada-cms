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

    /**
     * The draft actions in their intended order, for the head of an edit page.
     *
     * Spread these rather than listing them one by one, so the arrangement stays the same
     * across projects and a change to it arrives with the package:
     *
     *     protected function getHeaderActions(): array
     *     {
     *         return [
     *             ...$this->getDraftHeaderActions(),
     *             DeleteAction::make(),
     *         ];
     *     }
     *
     * Live preview is included when the page also uses `HasLivePreviewComponent`, so a
     * resource with no front-end view to preview can use drafts on their own.
     *
     * @return array<int, Action|ActionGroup>
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
            $this->getSwitchVersionAction(),
            $this->getSaveFormAction()->submit(null)->action('save'),
            $this->getSaveDraftAction(),
            $this->getPublishAction(),
        ]));
    }

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
