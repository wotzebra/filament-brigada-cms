<?php

namespace Wotz\FilamentBrigadaCms\Tests\Fixtures\Resources\Pages;

use Filament\Resources\Pages\CreateRecord;
use Wotz\FilamentBrigadaCms\Filament\Concerns\HandlesDraftsOnCreate;
use Wotz\FilamentBrigadaCms\Tests\Fixtures\Resources\PageResource;

class CreatePage extends CreateRecord
{
    use HandlesDraftsOnCreate;

    protected static string $resource = PageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ...$this->getDraftHeaderActions(),
            $this->getCancelFormAction(),
        ];
    }
}
