<?php

namespace Wotz\FilamentBrigadaCms\Tests\Fixtures\Resources\Pages;

use Filament\Resources\Pages\EditRecord;
use Wotz\FilamentBrigadaCms\Filament\Concerns\HandlesDraftsOnEdit;
use Wotz\FilamentBrigadaCms\Tests\Fixtures\Resources\PageResource;

class EditPage extends EditRecord
{
    use HandlesDraftsOnEdit;

    protected static string $resource = PageResource::class;

    protected function getHeaderActions(): array
    {
        return $this->getDraftHeaderActions();
    }
}
