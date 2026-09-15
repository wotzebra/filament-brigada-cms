<?php

namespace Wotz\FilamentBrigadaCms\Tests\Fixtures\Resources;

use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Wotz\FilamentBrigadaCms\Tests\Fixtures\Models\Article;
use Wotz\FilamentBrigadaCms\Tests\Fixtures\Resources\Pages\CreateArticle;
use Wotz\FilamentBrigadaCms\Tests\Fixtures\Resources\Pages\EditArticle;
use Wotz\FilamentBrigadaCms\Tests\Fixtures\Resources\Pages\ListArticles;

class ArticleResource extends Resource
{
    protected static ?string $model = Article::class;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title'),
            TextInput::make('amount_including_vat'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('title'),
            TextColumn::make('amount_including_vat'),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListArticles::route('/'),
            'create' => CreateArticle::route('/create'),
            'edit' => EditArticle::route('/{record}/edit'),
        ];
    }
}
