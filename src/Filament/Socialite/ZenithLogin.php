<?php

namespace Wotz\FilamentBrigadaCms\Filament\Socialite;

use DutchCodingCompany\FilamentSocialite\Events\Login;
use DutchCodingCompany\FilamentSocialite\FilamentSocialitePlugin;
use DutchCodingCompany\FilamentSocialite\Provider;
use Filament\Facades\Filament;
use Filament\Support\Colors\Color;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Laravel\Socialite\Contracts\User as SocialiteUserContract;
use Spatie\Permission\Models\Role;

/**
 * "Login for Brigada": WOTZ staff log in to any Brigada panel through Zenith.
 *
 * Whoever gets through Zenith is WOTZ staff, so they become a superadmin. A client
 * account is never linked to Zenith, so it can never be promoted this way: when the
 * email belongs to an account that isn't a superadmin already, the login is refused.
 * Offline accounts are refused too, so taking someone offline locks them out.
 */
class ZenithLogin
{
    public const PROVIDER = 'zenith';

    public static function provider(): Provider
    {
        return Provider::make(self::PROVIDER)
            ->label('Login for Brigada')
            ->color(Color::Gray)
            ->outlined()
            ->visible(fn (): bool => self::isConfigured());
    }

    public static function isConfigured(): bool
    {
        return filled(config('services.zenith.base_url')) &&
            filled(config('services.zenith.client_id')) &&
            filled(config('services.zenith.client_secret'));
    }

    public static function isCurrentProvider(): bool
    {
        return request()->route('provider') === self::PROVIDER;
    }

    public static function authorize(FilamentSocialitePlugin $plugin, SocialiteUserContract $oauthUser): bool
    {
        if (blank($oauthUser->getId()) || blank($oauthUser->getEmail())) {
            return false;
        }

        $linkedUser = self::linkedUser($plugin, (string) $oauthUser->getId());

        if ($linkedUser !== null) {
            return self::isOnline($linkedUser);
        }

        $user = self::userQuery($plugin)->where('email', $oauthUser->getEmail())->first();

        if ($user === null) {
            return true;
        }

        if (! self::isOnline($user)) {
            return false;
        }

        if (! self::isStaff($user)) {
            return false;
        }

        return ! self::socialiteUserQuery($plugin)
            ->where('provider', self::PROVIDER)
            ->where('user_id', $user->getKey())
            ->exists();
    }

    public static function createUser(FilamentSocialitePlugin $plugin, SocialiteUserContract $oauthUser): Authenticatable
    {
        $userModel = $plugin->getUserModelClass();

        /** @var Model&Authenticatable $user */
        $user = new $userModel;

        $user->forceFill([
            'name' => $oauthUser->getName() ?: $oauthUser->getEmail(),
            'email' => $oauthUser->getEmail(),
        ])->save();

        self::grantStaffRole($user);

        return $user;
    }

    /**
     * Keeps the name and email in step with Zenith, and the superadmin role in place,
     * on every login.
     */
    public static function syncStaff(Login $event): void
    {
        if (($event->socialiteUser->provider ?? null) !== self::PROVIDER) {
            return;
        }

        /** @var Model&Authenticatable $user */
        $user = $event->socialiteUser->getUser();

        $user->forceFill(array_filter([
            'name' => $event->oauthUser->getName(),
        ]));

        $email = $event->oauthUser->getEmail();

        if (filled($email) && ! $user->newQueryWithoutScopes()->where('email', $email)->whereKeyNot($user->getKey())->exists()) {
            $user->forceFill(['email' => $email]);
        }

        $user->save();

        self::grantStaffRole($user);
    }

    public static function isStaff(Authenticatable $user): bool
    {
        return method_exists($user, 'hasRole') && $user->hasRole(self::staffRole(), self::guard());
    }

    protected static function grantStaffRole(Authenticatable $user): void
    {
        if (! method_exists($user, 'assignRole')) {
            return;
        }

        /** @var class-string<Role> $roleModel */
        $roleModel = config('permission.models.role');

        $user->assignRole($roleModel::findOrCreate(self::staffRole(), self::guard()));
    }

    protected static function staffRole(): string
    {
        return config('filament-shield.super_admin.name', 'super_admin');
    }

    protected static function guard(): string
    {
        return Filament::getCurrentPanel()?->getAuthGuard() ?? config('auth.defaults.guard');
    }

    protected static function isOnline(Model $user): bool
    {
        if (! array_key_exists('online', $user->getAttributes())) {
            return true;
        }

        return (bool) $user->getAttribute('online');
    }

    protected static function linkedUser(FilamentSocialitePlugin $plugin, string $zenithId): ?Model
    {
        $userId = self::socialiteUserQuery($plugin)
            ->where('provider', self::PROVIDER)
            ->where('provider_id', $zenithId)
            ->value('user_id');

        if ($userId === null) {
            return null;
        }

        return self::userQuery($plugin)->find($userId);
    }

    /**
     * Without global scopes, so an account taken offline is still found — and refused —
     * instead of looking like a new user.
     *
     * @return Builder<Model>
     */
    protected static function userQuery(FilamentSocialitePlugin $plugin)
    {
        $userModel = $plugin->getUserModelClass();

        return (new $userModel)->newQueryWithoutScopes();
    }

    /**
     * @return Builder<Model>
     */
    protected static function socialiteUserQuery(FilamentSocialitePlugin $plugin)
    {
        $socialiteUserModel = $plugin->getSocialiteUserModelClass();

        return (new $socialiteUserModel)->newQuery();
    }
}
