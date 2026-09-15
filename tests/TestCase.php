<?php

namespace Wotz\FilamentBrigadaCms\Tests;

use BladeUI\Heroicons\BladeHeroiconsServiceProvider;
use BladeUI\Icons\BladeIconsServiceProvider;
use Filament\Actions\ActionsServiceProvider;
use Filament\Facades\Filament;
use Filament\FilamentServiceProvider;
use Filament\Forms\FormsServiceProvider;
use Filament\Infolists\InfolistsServiceProvider;
use Filament\Notifications\NotificationsServiceProvider;
use Filament\Schemas\SchemasServiceProvider;
use Filament\Support\SupportServiceProvider;
use Filament\Tables\TablesServiceProvider;
use Filament\Widgets\WidgetsServiceProvider;
use Illuminate\Support\Facades\Schema;
use Livewire\LivewireServiceProvider;
use Oddvalue\LaravelDrafts\LaravelDraftsServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Wezlo\FilamentSearchSpotlight\FilamentSearchSpotlightServiceProvider;
use Wotz\FilamentBrigadaCms\Providers\BrigadaCmsServiceProvider;
use Wotz\FilamentBrigadaCms\Tests\Fixtures\TestPanelProvider;

class TestCase extends Orchestra
{
    /**
     * Package config to apply before the providers boot.
     *
     * @var array<string, mixed>
     */
    protected array $packageConfig = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->createSchema();

        Filament::setCurrentPanel('admin');
    }

    protected function getPackageProviders($app)
    {
        return [
            SupportServiceProvider::class,
            ActionsServiceProvider::class,
            FormsServiceProvider::class,
            InfolistsServiceProvider::class,
            NotificationsServiceProvider::class,
            SchemasServiceProvider::class,
            TablesServiceProvider::class,
            WidgetsServiceProvider::class,
            BladeHeroiconsServiceProvider::class,
            BladeIconsServiceProvider::class,
            LivewireServiceProvider::class,
            LaravelDraftsServiceProvider::class,
            FilamentServiceProvider::class,
            FilamentSearchSpotlightServiceProvider::class,
            BrigadaCmsServiceProvider::class,
            TestPanelProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');

        foreach ($this->packageConfig as $key => $value) {
            config()->set($key, $value);
        }
    }

    /**
     * Rebuild the application with different package config.
     *
     * Every toggle this package has is read once, while the provider boots, so a
     * `config()->set()` inside a test arrives far too late to change anything.
     *
     * @param  array<string, mixed>  $config
     */
    public function rebootWithConfig(array $config): void
    {
        $this->packageConfig = $config;

        $this->refreshApplication();

        $this->createSchema();

        Filament::setCurrentPanel('admin');
    }

    protected function createSchema(): void
    {
        Schema::dropIfExists('articles');

        Schema::create('articles', function ($table): void {
            $table->id();
            $table->string('title')->nullable();
            $table->string('amount_including_vat')->nullable();
        });

        Schema::dropIfExists('pages');

        Schema::create('pages', function ($table): void {
            $table->id();
            $table->string('title')->nullable();
            $table->uuid('uuid')->nullable();
            $table->boolean('is_current')->default(false);
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->nullableMorphs('publisher');
            $table->timestamps();
        });
    }
}
