<?php

namespace Wotz\FilamentBrigadaCms\Tests\Fixtures;

use Filament\Panel;
use Filament\PanelProvider;
use Wotz\FilamentBrigadaCms\Filament\Socialite\BrigadaSocialitePlugin;
use Wotz\FilamentBrigadaCms\Filament\Socialite\ZenithLogin;
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
            ->login()
            ->plugin(
                BrigadaSocialitePlugin::make()->providers([
                    ZenithLogin::PROVIDER => ZenithLogin::provider(),
                ]),
            )
            ->resources([
                ArticleResource::class,
                SyncedArticleResource::class,
                PageResource::class,
            ]);
    }
}
