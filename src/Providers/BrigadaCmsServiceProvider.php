<?php

namespace Wotz\FilamentBrigadaCms\Providers;

use Filament\Facades\Filament;
use Filament\Forms\Components\Field;
use Filament\Infolists\Components\Entry;
use Filament\Navigation\NavigationGroup;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\Column;
use Filament\Tables\Table;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Wotz\FilamentBrigadaCms\Support\Labels;

class BrigadaCmsServiceProvider extends PackageServiceProvider
{
    protected const PACKAGE_NAME = 'filament-brigada-cms';

    public function configurePackage(Package $package): void
    {
        $package
            ->name($this->packageName())
            ->setBasePath(__DIR__ . '/../')
            ->hasConfigFile()
            ->hasViews($this->packageName())
            ->hasTranslations();
    }

    public function bootingPackage(): void
    {
        $this->configureTables();
        $this->configureLabels();
        $this->configureNavigationGroups();

        Filament::serving(function (): void {
            $this->configureFormatting();
        });
    }

    /**
     * Rows stay non-clickable so cell text — an article number, a title — can be selected
     * and copied straight out of the table; records open through each row's own actions.
     *
     * Registered outside `Filament::serving()` so it also applies under `Livewire::test`,
     * which never fires that event.
     */
    protected function configureTables(): void
    {
        if (! config('filament-brigada-cms.tables.enabled', true)) {
            return;
        }

        Table::configureUsing(function (Table $table): void {
            if (config('filament-brigada-cms.tables.non_clickable_rows', true)) {
                $table->recordUrl(null);
            }

            if (config('filament-brigada-cms.tables.persist_state', true)) {
                /*
                 * Filters already persist per table. Without the rest, a sort resets on
                 * every navigation, so half of a table's state survived and half did not.
                 */
                $table
                    ->persistSortInSession()
                    ->persistSearchInSession()
                    ->persistColumnSearchesInSession();
            }
        });
    }

    /**
     * Filament derives labels from component names, which lower-cases acronyms. These run
     * during `configure()` — after the name is set, before the component's own `setUp()` —
     * so they are only a default: an explicit `->label()` anywhere still wins.
     */
    protected function configureLabels(): void
    {
        if (! config('filament-brigada-cms.humanise_labels', true)) {
            return;
        }

        Column::configureUsing(fn (Column $column) => $column->label(Labels::humanise($column->getName())));
        Entry::configureUsing(fn (Entry $entry) => $entry->label(Labels::humanise($entry->getName())));
        Field::configureUsing(fn (Field $field) => $field->label(Labels::humanise($field->getName())));
    }

    protected function configureNavigationGroups(): void
    {
        if (! config('filament-brigada-cms.navigation.collapse_groups', true)) {
            return;
        }

        NavigationGroup::configureUsing(function (NavigationGroup $group): NavigationGroup {
            if (empty($group->getLabel())) {
                $group->label(__('filament-brigada-cms::cms.navigation.default_group'));
            }

            return $group->collapsed();
        });
    }

    /**
     * Display formats for dates and money. Deferred to `Filament::serving()` because they
     * are panel-scoped rather than global.
     */
    protected function configureFormatting(): void
    {
        $dateTime = config('filament-brigada-cms.formats.date_time');
        $date = config('filament-brigada-cms.formats.date');
        $currency = config('filament-brigada-cms.formats.currency');

        Table::configureUsing(function (Table $table) use ($dateTime, $date, $currency): void {
            $table
                ->paginated(config('filament-brigada-cms.tables.pagination_options', [10, 20, 50]))
                ->defaultPaginationPageOption(config('filament-brigada-cms.tables.default_pagination', 20))
                ->defaultDateTimeDisplayFormat($dateTime)
                ->defaultDateDisplayFormat($date)
                ->defaultCurrency($currency);
        });

        Schema::configureUsing(function (Schema $schema) use ($dateTime, $date, $currency): void {
            $schema
                ->defaultDateTimeDisplayFormat($dateTime)
                ->defaultDateDisplayFormat($date)
                ->defaultCurrency($currency);
        });
    }

    public function packageName(): string
    {
        return self::PACKAGE_NAME;
    }
}
