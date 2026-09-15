<?php

use Filament\Resources\Resource;
use Wotz\FilamentBrigadaCms\Concerns\HasReadOnlyBadge;

class SyncedResource extends Resource
{
    use HasReadOnlyBadge;
}

it('says in the sidebar that a synced resource cannot be edited', function () {
    // Translations resolve, rather than the raw key showing up in the sidebar, only
    // because the provider registers the package's `lang` directory.
    expect(SyncedResource::getNavigationBadge())->toBe('Read-only')
        ->and(SyncedResource::getNavigationBadgeTooltip())->toStartWith('This resource is synced')
        ->and(SyncedResource::getNavigationBadgeColor())->toBe('gray');
});

it('says it in the language the panel is in', function () {
    app()->setLocale('nl');

    expect(SyncedResource::getNavigationBadge())->toBe('Alleen-lezen');
});
