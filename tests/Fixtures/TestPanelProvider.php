<?php

namespace Wotz\FilamentBrigadaCms\Tests\Fixtures;

use Filament\Panel;
use Filament\PanelProvider;
use Wotz\FilamentBrigadaCms\Tests\Fixtures\Resources\ArticleResource;
use Wotz\FilamentBrigadaCms\Tests\Fixtures\Resources\PageResource;
use Wotz\FilamentBrigadaCms\Tests\Fixtures\Resources\SyncedArticleResource;

/**
 * A panel for the defaults to land in. Most of what this package does is a default
 * applied to a component, and a component only exists inside a panel.
 */
class TestPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->resources([
                ArticleResource::class,
                SyncedArticleResource::class,
                PageResource::class,
            ]);
    }
}
