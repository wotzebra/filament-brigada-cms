<?php

namespace Wotz\FilamentBrigadaCms\Tests\Fixtures\Resources\Pages;

use Filament\Resources\Pages\CreateRecord;
use Wotz\FilamentBrigadaCms\Tests\Fixtures\Resources\SyncedArticleResource;

class CreateSyncedArticle extends CreateRecord
{
    protected static string $resource = SyncedArticleResource::class;
}
