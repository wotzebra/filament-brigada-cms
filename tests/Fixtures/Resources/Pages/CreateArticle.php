<?php

namespace Wotz\FilamentBrigadaCms\Tests\Fixtures\Resources\Pages;

use Filament\Resources\Pages\CreateRecord;
use Wotz\FilamentBrigadaCms\Tests\Fixtures\Resources\ArticleResource;

class CreateArticle extends CreateRecord
{
    protected static string $resource = ArticleResource::class;
}
