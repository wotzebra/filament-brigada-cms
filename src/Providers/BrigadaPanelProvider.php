<?php

namespace Wotz\FilamentBrigadaCms\Providers;

use AlizHarb\ActivityLog\ActivityLogPlugin;
use Awcodes\StickyHeader\StickyHeaderPlugin;
use AzGasim\FilamentUnsavedChangesModal\FilamentUnsavedChangesModalPlugin;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Actions\Action;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Pboivin\FilamentPeek\FilamentPeekPlugin;
use pxlrbt\FilamentEnvironmentIndicator\EnvironmentIndicatorPlugin;
use Wallacemartinss\FilamentOnboarding\FilamentOnboardingPlugin;
use Wallacemartinss\FilamentOnboarding\Pages\OnboardingProgress;
use Wezlo\FilamentSearchSpotlight\Categories\RecordsCategory;
use Wezlo\FilamentSearchSpotlight\FilamentSearchSpotlightPlugin;
use Wotz\FilamentBrigadaCms\Filament\Spotlight\AccessAwareActionsCategory;
use Wotz\FilamentBrigadaCms\Filament\Spotlight\NavigationCategory;
use Wotz\FilamentBrigadaCms\Http\Middleware\DisableDraftPreview;
use Wotz\FilamentBrigadaTheme\Filament\BrigadaThemePlugin;
use Wotz\FilamentLivePreview\LivePreviewPlugin;
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
            ->plugins(array_values($this->plugins()))
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
     * The standard plugin set, keyed so a project can reconfigure one without rebuilding
     * the list. Plugin objects are mutable, so reach in and adjust:
     *
     *     $plugins = parent::plugins();
     *     $plugins['shield']->navigationGroup(NavigationGroup::General);
     *
     *     return [...$plugins, 'live-preview' => LivePreviewPlugin::make()];
     *
     * Drop one with `unset($plugins['seo'])`.
     *
     * @return array<string, mixed>
     */
    protected function plugins(): array
    {
        return [
            'theme' => BrigadaThemePlugin::make(),
            'shield' => FilamentShieldPlugin::make(),
            'activity-log' => ActivityLogPlugin::make(),
            'sticky-header' => StickyHeaderPlugin::make()->floating(),
            'translatable-strings' => TranslatableStringsPlugin::make(),
            'menu' => MenuPlugin::make(),
            'settings' => SettingsPlugin::make(),
            'media-library' => MediaLibraryPlugin::make(),
            'seo' => SeoPlugin::make(),
            'redirects' => RedirectsPlugin::make(),
            'environment-indicator' => EnvironmentIndicatorPlugin::make()->showBorder(false),
            'unsaved-changes' => FilamentUnsavedChangesModalPlugin::make(),
            'live-preview' => LivePreviewPlugin::make(),
            /*
             * Peek ships its own styles and scripts, which fight the theme's; live preview
             * only needs the modal machinery underneath.
             */
            'peek' => FilamentPeekPlugin::make()
                ->disablePluginScripts()
                ->disablePluginStyles(),
            'spotlight' => FilamentSearchSpotlightPlugin::make()->categories($this->spotlightCategories()),
            'onboarding' => FilamentOnboardingPlugin::make()
                ->manageFlows()
                ->progressPage(),
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
            /*
             * Drafts render a preview of the unpublished version on the front end; inside
             * the panel the current record is what should be edited.
             */
            DisableDraftPreview::class,
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
    /**
     * The panel builds its sidebar from NavigationGroup, and the onboarding page
     * belongs to none of those groups: it is about using the panel rather than
     * about any of the content in it. Left to the sidebar it has nowhere to
     * appear, and can be reached only by typing the URL.
     *
     * The user menu is where somebody looks for things about their own account
     * and their own way of working, which is what a guided tour is.
     */
    protected function userMenuItems(): array
    {
        return [
            Action::make('onboarding')
                /*
                 * Lazily, because this runs while the panel registers — before
                 * the package's own translations are. Resolved here and now, the
                 * lookup misses and the translator caches the whole `cms` group
                 * as empty for the rest of the request, taking every other
                 * string in it down too.
                 */
                ->label(fn (): string => __('filament-brigada-cms::cms.onboarding.menu'))
                ->icon(Heroicon::OutlinedAcademicCap)
                ->url(fn (): string => OnboardingProgress::getUrl())
                ->visible(fn (): bool => config('filament-brigada-cms.onboarding.user_menu', true)),
        ];
    }
}
