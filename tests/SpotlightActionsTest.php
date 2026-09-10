<?php

use Wezlo\FilamentSearchSpotlight\Actions\SpotlightAction;
use Wezlo\FilamentSearchSpotlight\Actions\SpotlightActionRegistry;
use Wezlo\FilamentSearchSpotlight\Categories\ActionsCategory;
use Wotz\FilamentBrigadaCms\Filament\Spotlight\AccessAwareActionsCategory;
use Wotz\FilamentBrigadaCms\Tests\Fixtures\Resources\ArticleResource;
use Wotz\FilamentBrigadaCms\Tests\Fixtures\Resources\PageResource;
use Wotz\FilamentBrigadaCms\Tests\Fixtures\Resources\SyncedArticleResource;

function actionNames(array $results): array
{
    return array_map(fn ($result) => $result->payload['action'], $results);
}

it('drops a create action the user is not allowed to reach', function () {
    // The plugin generates one per resource with a create page and never asks the
    // resource whether creating is allowed, so a read-only resource offered a shortcut
    // to a page that answers 403.
    $results = (new AccessAwareActionsCategory)->search('create', 10);

    expect(actionNames($results))->toEqualCanonicalizing([
        'create-' . md5(ArticleResource::class),
        'create-' . md5(PageResource::class),
    ]);
});

it('keeps everything else the plugin found', function () {
    $all = actionNames((new ActionsCategory)->search('create', 10));

    expect($all)->toContain('create-' . md5(SyncedArticleResource::class))
        ->and(actionNames((new AccessAwareActionsCategory)->search('create', 10)))
        ->not->toContain('create-' . md5(SyncedArticleResource::class));
});

it('leaves an action that has nothing to do with a resource alone', function () {
    app(SpotlightActionRegistry::class)->register(
        SpotlightAction::make('clear-cache')->label('Clear the cache')->url('/admin/clear-cache'),
    );

    expect(actionNames((new AccessAwareActionsCategory)->search('cache', 10)))->toBe(['clear-cache']);
});
