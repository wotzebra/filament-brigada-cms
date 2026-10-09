<?php

namespace Wotz\FilamentBrigadaCms\Filament\Socialite;

use Closure;
use DutchCodingCompany\FilamentSocialite\FilamentSocialitePlugin;
use Filament\Panel;
use Illuminate\Contracts\Auth\Authenticatable;
use Laravel\Socialite\Contracts\User as SocialiteUserContract;

/**
 * filament-socialite with the panel's own user model and the Zenith login for WOTZ
 * staff built in. Every other provider a project adds keeps the plugin's behaviour
 * and whatever it configures: the Zenith rules apply to the `zenith` provider only.
 */
class BrigadaSocialitePlugin extends FilamentSocialitePlugin
{
    protected bool $hasExplicitUserModel = false;

    public function userModelClass(string $value): static
    {
        $this->hasExplicitUserModel = true;

        return parent::userModelClass($value);
    }

    /**
     * The panel's guard is only final once the project's own `panel()` has run, so the
     * user model is read from it here rather than while the plugin is registered.
     */
    public function boot(Panel $panel): void
    {
        parent::boot($panel);

        if ($this->hasExplicitUserModel) {
            return;
        }

        $provider = config("auth.guards.{$panel->getAuthGuard()}.provider");

        $this->userModelClass = config("auth.providers.{$provider}.model", $this->userModelClass);
    }

    public function getAuthorizeUserUsing(): Closure
    {
        $authorizeUser = parent::getAuthorizeUserUsing();

        return function (FilamentSocialitePlugin $plugin, SocialiteUserContract $oauthUser) use ($authorizeUser): bool {
            if (ZenithLogin::isCurrentProvider()) {
                return ZenithLogin::authorize($plugin, $oauthUser);
            }

            return app()->call($authorizeUser, ['plugin' => $plugin, 'oauthUser' => $oauthUser]);
        };
    }

    public function getRegistration(): Closure|bool
    {
        $registration = parent::getRegistration();

        return function (string $provider, SocialiteUserContract $oauthUser, ?Authenticatable $user) use ($registration): bool {
            if ($provider === ZenithLogin::PROVIDER) {
                return true;
            }

            if (! $registration instanceof Closure) {
                return $registration;
            }

            return (bool) app()->call($registration, ['provider' => $provider, 'oauthUser' => $oauthUser, 'user' => $user]);
        };
    }

    public function getCreateUserUsing(): Closure
    {
        $createUser = parent::getCreateUserUsing();

        return function (string $provider, SocialiteUserContract $oauthUser, FilamentSocialitePlugin $plugin) use ($createUser): Authenticatable {
            if ($provider === ZenithLogin::PROVIDER) {
                return ZenithLogin::createUser($plugin, $oauthUser);
            }

            return app()->call($createUser, ['provider' => $provider, 'oauthUser' => $oauthUser, 'plugin' => $plugin]);
        };
    }
}
