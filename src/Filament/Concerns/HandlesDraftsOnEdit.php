<?php

namespace Wotz\FilamentBrigadaCms\Filament\Concerns;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;
use Wotz\FilamentBrigadaCms\Filament\Actions\CopyTranslationAction;
use Wotz\FilamentBrigadaCms\Filament\Actions\PublishAction;
use Wotz\FilamentBrigadaCms\Filament\Actions\SaveDraftAction;
use Wotz\FilamentBrigadaCms\Filament\Actions\SwitchVersionAction;

trait HandlesDraftsOnEdit
{
    protected bool $shouldSaveAsDraft = false;

    /**
     * The head of an edit page, in one arrangement.
     *
     * Spread these rather than listing them one by one, so the bar stays the same across
     * projects and a change to it arrives with the package:
     *
     *     protected function getHeaderActions(): array
     *     {
     *         return $this->getDraftHeaderActions();
     *     }
     *
     * It reads left to right as reading the record, then writing it:
     *
     *     [Preview v] [Version (3)]        [Copy translation] [PRIMARY v] [:]
     *
     * Four rules hold it together, and each one is a thing that had gone wrong: exactly
     * one filled button, so the page has a single obvious next step; exactly one unlabelled
     * menu, on the far right, so "where did that action go" has one answer; anything that
     * shows the record without changing it lives under Preview; and deleting is last,
     * alone, in red.
     *
     * A project adds its own through `getPreviewActions()` and `getOverflowActions()`
     * rather than by rebuilding the array, so its extras land in the right cluster.
     *
     * @return array<int, Action|ActionGroup>
     */
    protected function getDraftHeaderActions(): array
    {
        return array_values(array_filter([
            $this->getPreviewActionGroup(),
            $this->getSwitchVersionAction(),
            ...$this->getSaveActions(),
            $this->getOverflowActionGroup(),
        ]));
    }

    /**
     * Everything that shows the record without changing it.
     *
     * Live preview ships its own group, and it used to sit here as a second unlabelled
     * menu beside the real one. Its actions are unwrapped and re-grouped so there is one
     * menu for looking and one for the rest.
     *
     * @return array<int, Action>
     */
    protected function getPreviewActions(): array
    {
        return $this->getLivePreviewActions();
    }

    /**
     * The live-preview package's own two, unwrapped from the group it builds them in.
     *
     * A project overriding `getPreviewActions()` spreads this rather than reaching for
     * `parent::`, which does not resolve through a trait.
     *
     * @return array<int, Action>
     */
    protected function getLivePreviewActions(): array
    {
        return method_exists($this, 'getLivePreviewAction')
            ? $this->getLivePreviewAction()->getActions()
            : [];
    }

    protected function getPreviewActionGroup(): ?ActionGroup
    {
        $actions = array_values(array_filter($this->getPreviewActions()));

        if ($actions === []) {
            return null;
        }

        /*
         * The chevron rather than an eye: Filament gives a trigger one icon slot and
         * renders no caret of its own, so the slot goes to the thing that says this
         * opens a menu. A button that looks like a button and does not say it opens
         * anything is the one an editor clicks twice.
         */
        return ActionGroup::make($actions)
            ->label(__('filament-brigada-cms::cms.drafts.preview'))
            ->icon(Heroicon::ChevronDown)
            ->iconPosition(IconPosition::After)
            ->button()
            ->outlined()
            ->color('gray');
    }

    /**
     * Saving, in the one arrangement that fits the record's state.
     *
     * Read from a single answer to "is this published?" so the button and the menu behind
     * it cannot disagree — which is how a page ends up offering "Save to new draft" twice,
     * or offering it on a record that has no published version to draft against.
     *
     * On a draft the page exists to get the thing online, so Publish is the filled button
     * and saving is the quiet one beside it. On a published record the filled button makes
     * a new draft, because editing what is already on the site is the rarer and more
     * dangerous of the two — and plain saving moves under the caret rather than away.
     *
     * @return array<int, Action|ActionGroup>
     */
    protected function getSaveActions(): array
    {
        $isPublished = $this->getRecord()->isPublished();

        $save = $this->getSaveFormAction()->submit(null)->action('save');

        $primary = $isPublished
            ? $this->getSaveDraftAction()->color('primary')->icon(null)
            : $this->getPublishAction()->color('primary')->icon(null);

        $secondary = $isPublished
            ? $this->getCopyTranslationAction()
            : $save
                // Saving a draft saves the draft. "Save changes" is Filament's wording for
                // a record that has no other kind of save.
                ->label(__('filament-brigada-cms::cms.drafts.save'))
                ->color('gray')
                ->outlined();

        /*
         * The other ways to save, if there are any. Empty on a draft: saving one is
         * already the button next door, and there is nothing else to do to a record that
         * is not online yet.
         */
        $alternatives = array_values(array_filter([
            $isPublished ? $save : null,
        ]));

        /*
         * Filament lays the head out as one flat row, so the gap between reading the
         * record and writing it is made here: the first of the write actions takes the
         * slack. Without it the two halves run together and the bar reads as six
         * unrelated buttons.
         */
        $secondary?->extraAttributes(['style' => 'margin-inline-start: auto'], merge: true);

        /*
         * A menu holding one item is a button with an extra click in front of it, so a
         * lone alternative is simply shown. The caret earns its place from two.
         */
        $offered = count($alternatives) === 1
            ? [$alternatives[0]->color('gray')->outlined()]
            : array_filter([
                $alternatives === [] ? null : ActionGroup::make($alternatives)
                    ->icon(Heroicon::ChevronDown)
                    ->iconButton()
                    ->color('primary'),
            ]);

        return array_values(array_filter([
            $secondary,
            ...$offered,
            $primary,
        ]));
    }

    protected function getCopyTranslationAction(): ?Action
    {
        return CopyTranslationAction::make()->outlined();
    }

    /**
     * The one unlabelled menu, and the only place anything destructive lives.
     *
     * Delete is appended rather than offered to the project, so it is always last and
     * always red however many items a project adds above it.
     *
     * @return array<int, Action|ActionGroup>
     */
    protected function getOverflowActions(): array
    {
        return [];
    }

    protected function getOverflowActionGroup(): ?ActionGroup
    {
        $actions = array_values(array_filter([
            ...$this->getOverflowActions(),
            DeleteAction::make()->color('danger'),
        ]));

        if ($actions === []) {
            return null;
        }

        return ActionGroup::make($actions)
            ->icon(Heroicon::EllipsisVertical)
            ->iconButton()
            ->color('gray');
    }

    /**
     * The record's state, beside its title.
     *
     * State belongs to the record, so it is said once, where the record is named. It used
     * to be readable only by inferring it from the buttons — a page offering "Save to new
     * draft" is a published one — which asks an editor to reason backwards from what they
     * are about to press.
     *
     * Wraps whatever title the page already had rather than replacing it; a page that
     * declares its own `getTitle()` keeps it, and simply goes without the badge.
     */
    public function getTitle(): string|Htmlable
    {
        $title = parent::getTitle();

        return new HtmlString(
            ($title instanceof Htmlable ? $title->toHtml() : e($title))
            . view('filament-brigada-cms::record-state-badge', [
                'published' => $this->getRecord()->isPublished(),
            ])->render()
        );
    }

    protected function getSaveDraftAction(): Action
    {
        return SaveDraftAction::make('saveDraft');
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
