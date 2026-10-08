<?php

namespace Wotz\FilamentBrigadaCms\Tests;

use BladeUI\Heroicons\BladeHeroiconsServiceProvider;
use BladeUI\Icons\BladeIconsServiceProvider;
use DutchCodingCompany\FilamentSocialite\FilamentSocialiteServiceProvider;
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
use Laravel\Socialite\SocialiteServiceProvider;
use Livewire\LivewireServiceProvider;
use Oddvalue\LaravelDrafts\LaravelDraftsServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\Permission\PermissionServiceProvider;
use Wezlo\FilamentSearchSpotlight\FilamentSearchSpotlightServiceProvider;
use Wotz\FilamentBrigadaCms\Providers\BrigadaCmsServiceProvider;
use Wotz\FilamentBrigadaCms\Tests\Fixtures\Models\User;
use Wotz\FilamentBrigadaCms\Tests\Fixtures\TestPanelProvider;
use Wotz\SocialiteZenith\SocialiteZenithServiceProvider;

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
            PermissionServiceProvider::class,
            SocialiteServiceProvider::class,
            SocialiteZenithServiceProvider::class,
            FilamentSocialiteServiceProvider::class,
            BrigadaCmsServiceProvider::class,
            TestPanelProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');
        config()->set('auth.providers.users.model', User::class);

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
        Schema::dropIfExists('users');

        Schema::create('users', function ($table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password')->nullable();
            $table->boolean('online')->default(true);
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::dropIfExists('socialite_users');

        Schema::create('socialite_users', function ($table): void {
            $table->id();
            $table->foreignId('user_id');
            $table->string('provider');
            $table->string('provider_id');
            $table->timestamps();
            $table->unique(['provider', 'provider_id']);
        });

        if (! Schema::hasTable('roles')) {
            (require __DIR__ . '/../vendor/spatie/laravel-permission/database/migrations/create_permission_tables.php.stub')->up();
        }

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
