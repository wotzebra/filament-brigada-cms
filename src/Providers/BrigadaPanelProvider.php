<?php

namespace Wotz\FilamentBrigadaCms\Providers;

use AlizHarb\ActivityLog\ActivityLogPlugin;
use Awcodes\StickyHeader\StickyHeaderPlugin;
use AzGasim\FilamentUnsavedChangesModal\FilamentUnsavedChangesModalPlugin;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Enums\Width;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use pxlrbt\FilamentEnvironmentIndicator\EnvironmentIndicatorPlugin;
use Wezlo\FilamentSearchSpotlight\Categories\RecordsCategory;
use Wezlo\FilamentSearchSpotlight\FilamentSearchSpotlightPlugin;
use Wotz\FilamentBrigadaCms\Filament\Spotlight\AccessAwareActionsCategory;
use Wotz\FilamentBrigadaCms\Filament\Spotlight\NavigationCategory;
use Wotz\FilamentBrigadaTheme\Filament\BrigadaThemePlugin;
use Wotz\FilamentMenu\Filament\MenuPlugin;
use Wotz\FilamentRedirects\Filament\RedirectsPlugin;
use Wotz\FilamentSettings\Filament\SettingsPlugin;
use Wotz\MediaLibrary\Filament\MediaLibraryPlugin;
use Wotz\Seo\Filament\SeoPlugin;
use Wotz\TranslatableStrings\TranslatableStringsPlugin;

/**
 * The shape of a Brigada CMS panel. A project's own `AdminPanelProvider` extends this and
 * overrides only what differs — everything else keeps arriving through `composer update`.
 *
 * Every method below is a seam. Append rather than replace where you can:
 *
 *     protected function plugins(): array
 *     {
 *         return [...parent::plugins(), LivePreviewPlugin::make()];
 *     }
 */
abstract class BrigadaPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id($this->id())
            ->path($this->path())
            ->plugins($this->plugins())
            ->middleware($this->middleware())
            ->authMiddleware($this->authMiddleware())
            ->userMenuItems($this->userMenuItems())
            ->maxContentWidth(Width::Full)
            ->databaseNotifications()
            /*
             * Filament's own global search is replaced by the spotlight overlay, which also
             * covers navigation and actions rather than records alone.
             */
            ->globalSearch(false)
            ->renderHook(
                PanelsRenderHook::SIDEBAR_NAV_START,
                fn (): string => view('filament-brigada-cms::sidebar-nav-start')->render(),
            );
    }

    abstract protected function id(): string;

    protected function path(): string
    {
        return 'admin';
    }

    /**
     * The standard plugin set. Project-specific plugins belong in the subclass.
     *
     * @return array<int, mixed>
     */
    protected function plugins(): array
    {
        return [
            BrigadaThemePlugin::make(),
            FilamentShieldPlugin::make(),
            ActivityLogPlugin::make(),
            StickyHeaderPlugin::make()->floating(),
            TranslatableStringsPlugin::make(),
            MenuPlugin::make(),
            SettingsPlugin::make(),
            MediaLibraryPlugin::make(),
            SeoPlugin::make(),
            RedirectsPlugin::make(),
            EnvironmentIndicatorPlugin::make()->showBorder(false),
            FilamentUnsavedChangesModalPlugin::make(),
            FilamentSearchSpotlightPlugin::make()->categories($this->spotlightCategories()),
        ];
    }

    /**
     * @return array<int, class-string>
     */
    protected function spotlightCategories(): array
    {
        return [
            NavigationCategory::class,
            RecordsCategory::class,
            AccessAwareActionsCategory::class,
        ];
    }

    /**
     * @return array<int, class-string>
     */
    protected function middleware(): array
    {
        return [
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
            AuthenticateSession::class,
            ShareErrorsFromSession::class,
            PreventRequestForgery::class,
            SubstituteBindings::class,
            DisableBladeIconComponents::class,
            DispatchServingFilamentEvent::class,
        ];
    }

    /**
     * @return array<int, class-string>
     */
    protected function authMiddleware(): array
    {
        return [
            Authenticate::class,
        ];
    }

    /**
     * @return array<int, mixed>
     */
    protected function userMenuItems(): array
    {
        return [];
    }
}
