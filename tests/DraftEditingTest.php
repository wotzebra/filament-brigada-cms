<?php

use Livewire\Livewire;
use Wotz\FilamentBrigadaCms\Tests\Fixtures\Models\Page;
use Wotz\FilamentBrigadaCms\Tests\Fixtures\Resources\PageResource;
use Wotz\FilamentBrigadaCms\Tests\Fixtures\Resources\Pages\CreatePage;
use Wotz\FilamentBrigadaCms\Tests\Fixtures\Resources\Pages\EditPage;

function publishedPage(string $title = 'Contact'): Page
{
    // `laravel-drafts` publishes on create, so a live page is the plain one.
    return Page::create(['title' => $title])->fresh();
}

function draftPage(string $title = 'Over ons'): Page
{
    $page = new Page(['title' => $title]);
    $page->is_published = false;
    $page->save();

    return $page->fresh();
}

it('creates a page as a draft rather than putting it on the site', function () {
    // The create form's own button is relabelled "Create draft", and nothing on the page
    // publishes: a new page is never live the moment somebody presses save.
    Livewire::test(CreatePage::class)
        ->fillForm(['title' => 'Over ons'])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Page::withDrafts()->sole())
        ->title->toBe('Over ons')
        ->is_published->toBeFalse();
});

it('saves an edit to a live page into a new draft, leaving the live one alone', function () {
    $page = publishedPage();

    Livewire::test(EditPage::class, ['record' => $page->getKey()])
        ->fillForm(['title' => 'Contacteer ons'])
        ->callAction('saveDraft');

    expect($page->fresh())
        ->title->toBe('Contact')
        ->is_published->toBeTrue()
        ->and(Page::withDrafts()->where('is_current', true)->sole())
        ->title->toBe('Contacteer ons')
        ->is_published->toBeFalse();
});

it('carries on editing the draft it just made, not the live page', function () {
    $page = publishedPage();

    Livewire::test(EditPage::class, ['record' => $page->getKey()])
        ->fillForm(['title' => 'Contacteer ons'])
        ->callAction('saveDraft')
        ->assertRedirect(PageResource::getUrl('edit', [
            'record' => Page::withDrafts()->where('is_current', true)->sole(),
        ]));
});

it('edits an unpublished page in place instead of piling up drafts', function () {
    $page = draftPage();

    Livewire::test(EditPage::class, ['record' => $page->getKey()])
        ->fillForm(['title' => 'Over ons en onze mensen'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(Page::withDrafts()->sole())->title->toBe('Over ons en onze mensen');
});

it('publishes the draft that is open', function () {
    $page = draftPage();

    Livewire::test(EditPage::class, ['record' => $page->getKey()])
        ->callAction('publish');

    expect($page->fresh())->is_published->toBeTrue();
});

it('offers publishing only while there is something unpublished to publish', function () {
    Livewire::test(EditPage::class, ['record' => draftPage()->getKey()])
        ->assertActionVisible('publish')
        // A draft has no live version to draft against, so the bar leaves the button out.
        ->assertActionDoesNotExist('saveDraft');

    Livewire::test(EditPage::class, ['record' => publishedPage()->getKey()])
        ->assertActionDoesNotExist('publish')
        // Saving over a live page is what the draft button is there to avoid.
        ->assertActionVisible('saveDraft');
});

it('offers every other version to switch to, and not the one already open', function () {
    $live = publishedPage();

    Livewire::test(EditPage::class, ['record' => $live->getKey()])
        ->fillForm(['title' => 'Contacteer ons'])
        ->callAction('saveDraft');

    $draft = Page::withDrafts()->where('is_current', true)->sole();

    Livewire::test(EditPage::class, ['record' => $draft->getKey()])
        ->assertActionEnabled("switchVersion_{$live->getKey()}")
        ->assertActionDisabled("switchVersion_{$draft->getKey()}");
});
