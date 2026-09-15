<?php

namespace Wotz\FilamentBrigadaCms\Tests\Fixtures\Resources\Pages;

use Filament\Resources\Pages\ListRecords;
use Wotz\FilamentBrigadaCms\Tests\Fixtures\Resources\SyncedArticleResource;

class ListSyncedArticles extends ListRecords
{
    protected static string $resource = SyncedArticleResource::class;
}
