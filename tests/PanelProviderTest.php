<?php

use Wotz\FilamentBrigadaCms\Filament\Spotlight\AccessAwareActionsCategory;
use Wotz\FilamentBrigadaCms\Filament\Spotlight\NavigationCategory;
use Wotz\FilamentBrigadaCms\Http\Middleware\DisableDraftPreview;
use Wotz\FilamentBrigadaCms\Tests\Fixtures\ProjectPanelProvider;

function projectPanel(): ProjectPanelProvider
{
    return new ProjectPanelProvider(app());
}

it('keys its plugins so a project can reconfigure one without rebuilding the list', function () {
    // The keys are the documented seam — `unset($plugins['seo'])`, or reach in and
    // reconfigure `$plugins['shield']`. Renaming one silently breaks every project.
    expect(array_keys(projectPanel()->pluginSet()))->toContain(
        'theme', 'shield', 'menu', 'settings', 'media-library', 'seo', 'redirects', 'spotlight',
    );
});

it('turns draft preview off for everything behind the panel', function () {
    expect(projectPanel()->middlewareStack())->toContain(DisableDraftPreview::class);
});

it('searches through the categories that respect permissions', function () {
    expect(projectPanel()->spotlightCategorySet())
        ->toContain(NavigationCategory::class)
        ->toContain(AccessAwareActionsCategory::class);
});
