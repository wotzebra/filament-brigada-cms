<?php

namespace Wotz\FilamentBrigadaCms\Tests\Fixtures\Resources;

use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Wotz\FilamentBrigadaCms\Tests\Fixtures\Models\Page;
use Wotz\FilamentBrigadaCms\Tests\Fixtures\Resources\Pages\CreatePage;
use Wotz\FilamentBrigadaCms\Tests\Fixtures\Resources\Pages\EditPage;
use Wotz\FilamentBrigadaCms\Tests\Fixtures\Resources\Pages\ListPages;

class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title'),
        ]);
    }

    /**
     * Drafts are hidden by a global scope, so without this the edit page cannot find the
     * very record its draft actions exist to work on.
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withDrafts();
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPages::route('/'),
            'create' => CreatePage::route('/create'),
            'edit' => EditPage::route('/{record}/edit'),
        ];
    }
}
