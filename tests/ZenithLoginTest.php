<?php

use DutchCodingCompany\FilamentSocialite\Models\SocialiteUser;
use DutchCodingCompany\FilamentSocialite\Provider;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Http;
use Illuminate\Testing\TestResponse;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUserData;
use Spatie\Permission\Models\Role;
use Wotz\FilamentBrigadaCms\Tests\Fixtures\Models\User;
use Wotz\FilamentBrigadaCms\Tests\Fixtures\ProjectPanelProvider;

use function Pest\Laravel\assertAuthenticatedAs;
use function Pest\Laravel\assertGuest;

beforeEach(function () {
    config()->set('services.zenith', [
        'base_url' => 'https://zenith.test',
        'client_id' => 'zenith-client',
        'client_secret' => 'zenith-secret',
        'redirect' => 'http://localhost/admin/oauth/callback/zenith',
    ]);
});

function fakeZenith(int $userStatus = 200): void
{
    Http::preventStrayRequests();

    Http::fake([
        'zenith.test/oauth/token' => Http::response(['token_type' => 'Bearer', 'access_token' => 'zenith-access-token']),
        'zenith.test/api/user/current' => $userStatus === 200
            ? Http::response(['data' => ['id' => 42, 'firstname' => 'Wim', 'lastname' => 'Zebra', 'email' => 'wim@wotz.be']])
            : Http::response(['message' => 'Your account has been deactivated.'], $userStatus),
    ]);
}

function zenithCallback(string $provider = 'zenith'): TestResponse
{
    return test()
        ->withSession(['state' => 'valid-state'])
        ->get("/admin/oauth/callback/{$provider}?code=auth-code&state=valid-state");
}

function linkToZenith(User $user, string $zenithId = '42'): void
{
    SocialiteUser::query()->create(['user_id' => $user->id, 'provider' => 'zenith', 'provider_id' => $zenithId]);
}

function superAdmin(array $attributes = []): User
{
    $user = User::query()->create(['name' => 'Staff', 'email' => 'wim@wotz.be', ...$attributes]);

    $user->assignRole(Role::findOrCreate('super_admin', 'web'));

    return $user;
}

it('adds the socialite plugin to every Brigada panel', function () {
    expect((new ProjectPanelProvider(app()))->pluginSet())->toHaveKey('socialite');
});

it('shows the Zenith button once Zenith is configured', function () {
    $this->get(Filament::getPanel('admin')->getLoginUrl())
        ->assertOk()
        ->assertSee('Login for Brigada')
        ->assertSee('M83.8866 123H24.2806', escape: false);
});

it('hides the Zenith button while Zenith is not fully configured', function () {
    config()->set('services.zenith.client_secret', null);

    $this->get(Filament::getPanel('admin')->getLoginUrl())
        ->assertOk()
        ->assertDontSee('Login for Brigada');
});

it('creates a new Zenith user as superadmin and logs them in', function () {
    fakeZenith();

    zenithCallback()->assertRedirect(Filament::getPanel('admin')->getUrl());

    $dbUser = User::query()->where('email', 'wim@wotz.be')->sole();

    expect($dbUser)
        ->name->toBe('Wim Zebra')
        ->password->toBeNull()
        ->and($dbUser->hasRole('super_admin'))->toBeTrue()
        ->and(SocialiteUser::query()->where('provider_id', '42')->value('user_id'))->toBe($dbUser->id);

    assertAuthenticatedAs($dbUser);
});

it('updates a linked Zenith user from Zenith on every login', function () {
    $user = User::query()->create(['name' => 'Old Name', 'email' => 'old@wotz.be']);
    linkToZenith($user);

    fakeZenith();

    zenithCallback()->assertRedirect(Filament::getPanel('admin')->getUrl());

    $dbUser = User::query()->find($user->id);

    expect($dbUser)
        ->name->toBe('Wim Zebra')
        ->email->toBe('wim@wotz.be')
        ->and($dbUser->hasRole('super_admin'))->toBeTrue();

    assertAuthenticatedAs($dbUser);
});

it('links an existing superadmin with the same email', function () {
    $user = superAdmin();

    fakeZenith();

    zenithCallback()->assertRedirect(Filament::getPanel('admin')->getUrl());

    expect(SocialiteUser::query()->where('provider_id', '42')->value('user_id'))->toBe($user->id)
        ->and(User::query()->count())->toBe(1);

    assertAuthenticatedAs($user);
});

it('refuses a client account with the same email and does not promote it', function () {
    $user = User::query()->create(['name' => 'Client', 'email' => 'wim@wotz.be']);

    fakeZenith();

    zenithCallback()
        ->assertRedirect(Filament::getPanel('admin')->getLoginUrl())
        ->assertSessionHas('filament-socialite-login-error', "This account can't log in through Zenith.");

    expect(User::query()->find($user->id)->hasRole('super_admin'))->toBeFalse()
        ->and(SocialiteUser::query()->count())->toBe(0);

    assertGuest();
});

it('refuses a superadmin already linked to another Zenith user', function () {
    linkToZenith(superAdmin(), zenithId: '7');

    fakeZenith();

    zenithCallback()->assertRedirect(Filament::getPanel('admin')->getLoginUrl());

    assertGuest();
});

it('refuses a linked Zenith user who has been taken offline', function () {
    $user = User::query()->create(['name' => 'Wim', 'email' => 'wim@wotz.be', 'online' => false]);
    linkToZenith($user);

    fakeZenith();

    zenithCallback()->assertRedirect(Filament::getPanel('admin')->getLoginUrl());

    assertGuest();
});

it('sends a user deactivated in Zenith back to the login form', function () {
    fakeZenith(userStatus: 403);

    zenithCallback()
        ->assertRedirect(Filament::getPanel('admin')->getLoginUrl())
        ->assertSessionHas('filament-socialite-login-error', 'Your Zenith account is deactivated.');

    expect(User::query()->count())->toBe(0);
    assertGuest();
});

it('sends the user back to the login form when Zenith fails', function () {
    fakeZenith(userStatus: 500);

    zenithCallback()
        ->assertRedirect(Filament::getPanel('admin')->getLoginUrl())
        ->assertSessionHas('filament-socialite-login-error', 'Login failed, please try again.');

    assertGuest();
});

it('keeps filament-socialite rules for a project provider', function () {
    config()->set('services.github', ['client_id' => 'x', 'client_secret' => 'y', 'redirect' => '/admin/oauth/callback/github']);

    $plugin = Filament::getPanel('admin')->getPlugin('filament-socialite');
    $plugin->providers([...$plugin->getProviders(), Provider::make('github')]);

    Socialite::fake('github', (new SocialiteUserData)->map(['id' => '1', 'name' => 'Someone', 'email' => 'someone@example.com']));

    zenithCallback('github')
        ->assertRedirect(Filament::getPanel('admin')->getLoginUrl())
        ->assertSessionHas('filament-socialite-login-error', 'Registration of a new user is not allowed.');

    expect(User::query()->count())->toBe(0);
});
