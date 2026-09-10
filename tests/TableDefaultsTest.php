<?php

use Filament\Facades\Filament;
use Filament\Tables\Table;
use Livewire\Livewire;
use Wotz\FilamentBrigadaCms\Tests\Fixtures\Models\Article;
use Wotz\FilamentBrigadaCms\Tests\Fixtures\Resources\Pages\ListArticles;

function articleTable(): Table
{
    return Livewire::test(ListArticles::class)->instance()->getTable();
}

it('keeps table rows non-clickable so cell text can be copied', function () {
    $article = Article::create(['title' => 'Cedral Click C05']);

    // A resource list page fills in the edit URL for every row unless the table already
    // has one of its own, and a row that navigates on click cannot have its text
    // selected. Records open through the row's own actions instead.
    expect(articleTable()->getRecordUrl($article))->toBeNull();
});

it('lets a project have clickable rows back', function () {
    $this->rebootWithConfig(['filament-brigada-cms.tables.non_clickable_rows' => false]);

    $article = Article::create(['title' => 'Cedral Click C05']);

    expect(articleTable()->getRecordUrl($article))->toEndWith("/admin/articles/{$article->id}/edit");
});

it('persists sort and search so a table looks the same when you come back', function () {
    // Filters persist on their own. Without the rest a sort resets on every navigation,
    // so half of a table's state survived and half did not.
    $table = articleTable();

    expect($table->persistsSortInSession())->toBeTrue()
        ->and($table->persistsSearchInSession())->toBeTrue()
        ->and($table->persistsColumnSearchesInSession())->toBeTrue();
});

it('stops persisting table state when a project turns it off', function () {
    $this->rebootWithConfig(['filament-brigada-cms.tables.persist_state' => false]);

    $table = articleTable();

    expect($table->persistsSortInSession())->toBeFalse()
        ->and($table->persistsSearchInSession())->toBeFalse()
        ->and($table->persistsColumnSearchesInSession())->toBeFalse();
});

it('leaves every table alone when a project disables the table defaults', function () {
    $this->rebootWithConfig(['filament-brigada-cms.tables.enabled' => false]);

    $article = Article::create(['title' => 'Cedral Click C05']);
    $table = articleTable();

    expect($table->getRecordUrl($article))->not->toBeNull()
        ->and($table->persistsSortInSession())->toBeFalse();
});

it('configures tables under Livewire::test, where Filament::serving never fires', function () {
    $article = Article::create(['title' => 'Cedral Click C05']);

    /*
     * The reason `configureTables()` sits outside `Filament::serving()`. That event is
     * dispatched by panel middleware, and `Livewire::test` mounts a page without going
     * through it — so table defaults registered there would be silently absent from every
     * test a project writes about its own panel.
     */
    expect(Filament::getCurrentPanel())->not->toBeNull()
        ->and(articleTable()->getRecordUrl($article))->toBeNull();
});
