<?php

declare(strict_types=1);

namespace Wotz\FilamentBrigadaCms\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;
use Wallacemartinss\FilamentOnboarding\Models\OnboardingFlow;

/**
 * The plugin ships permissive policies on purpose — everyone who can reach the
 * panel may write journeys, until the application says otherwise. This says
 * otherwise, the same way every other resource here does: through Shield.
 *
 * Registered in `config/filament-onboarding.php`, which the plugin reads in
 * preference to its own.
 */
class OnboardingFlowPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:OnboardingFlow');
    }

    public function view(AuthUser $authUser, OnboardingFlow $onboardingFlow): bool
    {
        return $authUser->can('View:OnboardingFlow');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:OnboardingFlow');
    }

    public function update(AuthUser $authUser, OnboardingFlow $onboardingFlow): bool
    {
        return $authUser->can('Update:OnboardingFlow');
    }

    public function delete(AuthUser $authUser, OnboardingFlow $onboardingFlow): bool
    {
        return $authUser->can('Delete:OnboardingFlow');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('Delete:OnboardingFlow');
    }

    public function restore(AuthUser $authUser, OnboardingFlow $onboardingFlow): bool
    {
        return $authUser->can('Delete:OnboardingFlow');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('Delete:OnboardingFlow');
    }

    public function forceDelete(AuthUser $authUser, OnboardingFlow $onboardingFlow): bool
    {
        return $authUser->can('Delete:OnboardingFlow');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('Delete:OnboardingFlow');
    }

    public function replicate(AuthUser $authUser, OnboardingFlow $onboardingFlow): bool
    {
        return $authUser->can('Create:OnboardingFlow');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:OnboardingFlow');
    }
}
