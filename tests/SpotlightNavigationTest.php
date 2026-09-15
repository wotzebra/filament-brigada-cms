<?php

use Filament\Facades\Filament;
use Filament\Navigation\NavigationItem;
use Wotz\FilamentBrigadaCms\Filament\Spotlight\NavigationCategory;

beforeEach(function () {
    Filament::getCurrentPanel()->navigationItems([
        NavigationItem::make('Vacancies')->url('/admin/vacancies')->group('Content'),
        NavigationItem::make('Dealers')->url('/admin/dealers')->group('Content'),
        NavigationItem::make('Redirects')->url('/admin/redirects')->group('Settings'),
    ]);
});

it('finds a sidebar item by its own name', function () {
    $results = (new NavigationCategory)->search('vacan', 10);

    expect($results)->toHaveCount(1)
        ->and($results[0]->title)->toBe('Vacancies')
        ->and($results[0]->subtitle)->toBe('Content')
        ->and($results[0]->url)->toBe('/admin/vacancies');
});

it('finds every item in a group by the name of the group', function () {
    // Somebody who remembers a page lives under "Content" but not what it is called.
    $titles = array_map(fn ($result) => $result->title, (new NavigationCategory)->search('content', 10));

    expect($titles)->toContain('Vacancies', 'Dealers')
        ->and($titles)->not->toContain('Redirects');
});

it('searches only what the sidebar already decided to show', function () {
    /*
     * The point of the category. The plugin's own resources and pages categories walk the
     * registered classes, which lists things the signed-in user has no access to; the
     * sidebar is already filtered by then.
     */
    $titles = array_map(fn ($result) => $result->title, (new NavigationCategory)->search('', 50));
    $sidebar = collect(Filament::getNavigation())
        ->flatMap(fn ($group) => $group->getItems())
        ->map(fn (NavigationItem $item) => $item->getLabel())
        ->all();

    expect($titles)->toEqualCanonicalizing($sidebar);
});

it('offers nothing for a query that matches neither an item nor a group', function () {
    expect((new NavigationCategory)->search('invoices', 10))->toBe([]);
});

it('stops at the limit it was given', function () {
    expect((new NavigationCategory)->search('', 2))->toHaveCount(2);
});
