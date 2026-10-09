<?php

namespace Wotz\FilamentBrigadaCms\Http\Controllers;

use DutchCodingCompany\FilamentSocialite\Http\Controllers\SocialiteLoginController as BaseSocialiteLoginController;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Client\HttpClientException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\RedirectResponse;
use Laravel\Socialite\Contracts\User as SocialiteUserContract;
use Wotz\FilamentBrigadaCms\Filament\Socialite\ZenithLogin;

/**
 * filament-socialite only catches an invalid state: any other failure of the request
 * to the provider (access denied, provider down, a user deactivated in Zenith) ends
 * in a 500 page. Those send the user back to the login form instead, with a message
 * that says what happened for Zenith.
 */
class SocialiteLoginController extends BaseSocialiteLoginController
{
    protected ?string $failureMessage = null;

    protected function retrieveOauthUser(string $provider): ?SocialiteUserContract
    {
        try {
            return parent::retrieveOauthUser($provider);
        } catch (RequestException $exception) {
            if ($provider === ZenithLogin::PROVIDER && $exception->response->forbidden()) {
                $this->failureMessage = 'filament-brigada-cms::cms.zenith.deactivated';

                return null;
            }

            report($exception);
        } catch (GuzzleException|HttpClientException $exception) {
            report($exception);
        }

        return null;
    }

    protected function redirectToLogin(string $message): RedirectResponse
    {
        if ($this->failureMessage !== null) {
            return parent::redirectToLogin($this->failureMessage);
        }

        if (ZenithLogin::isCurrentProvider() && $message === 'filament-socialite::auth.user-not-allowed') {
            return parent::redirectToLogin('filament-brigada-cms::cms.zenith.not_allowed');
        }

        return parent::redirectToLogin($message);
    }
}
