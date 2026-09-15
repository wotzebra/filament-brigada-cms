<?php

namespace Wotz\FilamentBrigadaCms\Tests\Fixtures\Resources;

use Filament\Resources\Resource;
use Wotz\FilamentBrigadaCms\Tests\Fixtures\Models\Article;
use Wotz\FilamentBrigadaCms\Tests\Fixtures\Resources\Pages\CreateSyncedArticle;
use Wotz\FilamentBrigadaCms\Tests\Fixtures\Resources\Pages\ListSyncedArticles;

/**
 * A resource whose records arrive from somewhere else, so nobody may create one here.
 */
class SyncedArticleResource extends Resource
{
    protected static ?string $model = Article::class;

    protected static ?string $slug = 'synced-articles';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSyncedArticles::route('/'),
            'create' => CreateSyncedArticle::route('/create'),
        ];
    }
}
