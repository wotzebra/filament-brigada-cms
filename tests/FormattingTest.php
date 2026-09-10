<?php

use Filament\Events\ServingFilament;
use Filament\Schemas\Schema;
use Livewire\Livewire;
use Wotz\FilamentBrigadaCms\Tests\Fixtures\Resources\Pages\ListArticles;

it('formats dates and money the way the Low Countries write them', function () {
    ServingFilament::dispatch();

    $table = Livewire::test(ListArticles::class)->instance()->getTable();

    expect($table->getDefaultDateDisplayFormat())->toBe('d M Y')
        ->and($table->getDefaultDateTimeDisplayFormat())->toBe('d M Y H:i:s')
        ->and($table->getDefaultCurrency())->toBe('EUR');
});

it('formats a form the same way it formats a table', function () {
    ServingFilament::dispatch();

    $schema = Schema::make(Livewire::test(ListArticles::class)->instance());

    expect($schema->getDefaultDateDisplayFormat())->toBe('d M Y')
        ->and($schema->getDefaultDateTimeDisplayFormat())->toBe('d M Y H:i:s')
        ->and($schema->getDefaultCurrency())->toBe('EUR');
});

it('paginates in the sizes an editor actually wants', function () {
    ServingFilament::dispatch();

    $table = Livewire::test(ListArticles::class)->instance()->getTable();

    expect($table->getPaginationPageOptions())->toBe([10, 20, 50])
        ->and($table->getDefaultPaginationPageOption())->toBe(20);
});

it('takes its formats from config', function () {
    $this->rebootWithConfig([
        'filament-brigada-cms.formats.date' => 'Y-m-d',
        'filament-brigada-cms.formats.currency' => 'GBP',
        'filament-brigada-cms.tables.default_pagination' => 100,
    ]);

    ServingFilament::dispatch();

    $table = Livewire::test(ListArticles::class)->instance()->getTable();

    expect($table->getDefaultDateDisplayFormat())->toBe('Y-m-d')
        ->and($table->getDefaultCurrency())->toBe('GBP')
        ->and($table->getDefaultPaginationPageOption())->toBe(100);
});

it('has no formats at all until a panel is served', function () {
    /*
     * The counterpart to the table defaults, which are deliberately registered outside
     * `Filament::serving()`. Nothing dispatches that event under `Livewire::test`, so a
     * project asserting on a formatted date in its own tests is asserting on Filament's
     * "M j, Y" and dollars, not on anything this package configured.
     */
    $table = Livewire::test(ListArticles::class)->instance()->getTable();

    expect($table->getDefaultDateDisplayFormat())->toBe('M j, Y')
        ->and($table->getDefaultCurrency())->toBe('usd');
});
