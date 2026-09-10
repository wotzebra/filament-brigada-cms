<?php

namespace Wotz\FilamentBrigadaCms\Tests\Fixtures\Resources\Pages;

use Filament\Resources\Pages\EditRecord;
use Wotz\FilamentBrigadaCms\Tests\Fixtures\Resources\ArticleResource;

class EditArticle extends EditRecord
{
    protected static string $resource = ArticleResource::class;
}
