<?php

declare(strict_types=1);

namespace Wotz\FilamentBrigadaCms\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;
use Wallacemartinss\FilamentOnboarding\Models\OnboardingCondition;

/**
 * The plugin ships permissive policies on purpose — everyone who can reach the
 * panel may write journeys, until the application says otherwise. This says
 * otherwise, the same way every other resource here does: through Shield.
 *
 * Registered in `config/filament-onboarding.php`, which the plugin reads in
 * preference to its own.
 */
class OnboardingConditionPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:OnboardingCondition');
    }

    public function view(AuthUser $authUser, OnboardingCondition $onboardingCondition): bool
    {
        return $authUser->can('View:OnboardingCondition');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:OnboardingCondition');
    }

    public function update(AuthUser $authUser, OnboardingCondition $onboardingCondition): bool
    {
        return $authUser->can('Update:OnboardingCondition');
    }

    public function delete(AuthUser $authUser, OnboardingCondition $onboardingCondition): bool
    {
        return $authUser->can('Delete:OnboardingCondition');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('Delete:OnboardingCondition');
    }

    public function restore(AuthUser $authUser, OnboardingCondition $onboardingCondition): bool
    {
        return $authUser->can('Delete:OnboardingCondition');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('Delete:OnboardingCondition');
    }

    public function forceDelete(AuthUser $authUser, OnboardingCondition $onboardingCondition): bool
    {
        return $authUser->can('Delete:OnboardingCondition');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('Delete:OnboardingCondition');
    }

    public function replicate(AuthUser $authUser, OnboardingCondition $onboardingCondition): bool
    {
        return $authUser->can('Create:OnboardingCondition');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:OnboardingCondition');
    }
}
