<?php
namespace App\Policies;

use App\Contracts\BelongsToBusinessesInterface;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

abstract class BasePolicy
{
    use HandlesAuthorization;

    ////////////////////////////////////
    //// Shortcut methods
    ////////////////////////////////////

    public function view($user, ...$args)
    {
        return $this->read($user, ...$args);
    }

    public function show($user, ...$args)
    {
        return $this->read($user, ...$args);
    }

    public function destroy($user, ...$args)
    {
        return $this->delete($user, ...$args);
    }

    public function store($user, ...$args)
    {
        return $this->create($user, ...$args);
    }

    ////////////////////////////////////
    //// Authenticated user role checks
    ////////////////////////////////////

    protected function isAdmin()
    {
        return auth()->check() && auth()->user()->role_type === 'admin';
    }

    protected function isOfficeUser()
    {
        return auth()->check() && auth()->user()->role_type === 'office_user';
    }

    protected function isCaregiver()
    {
        return auth()->check() && auth()->user()->role_type === 'caregiver';
    }

    protected function isClient()
    {
        return auth()->check() && auth()->user()->role_type === 'client';
    }

    ////////////////////////////////////
    //// Common access checks
    ////////////////////////////////////

    /**
     * Re-usable check for business-owned entities
     *
     * @param \App\User $user
     * @param \App\Contracts\BelongsToBusinessesInterface $entity
     * @return bool
     */
    protected function businessCheck(User $user, $entity)
    {
        if ($entity->user_id == $user->id) {
            return true;
        }

        if ($this->isAdmin()) {
            return true;
        }

        if ($this->isOfficeUser() && $user->sharesBusinessWith($entity)) {
            return true;
        }

        return false;
    }

    protected function businessChainCheck(User $user, $entity)
    {
        if ($entity->user_id == $user->id) {
            return true;
        }

        if ($this->isAdmin()) {
            return true;
        }

        if ($this->isOfficeUser() && $user->officeUser->sharesChainWith($entity)) {
            return true;
        }

        return false;
    }

    protected function caregiverCheck(User $user, $entity)
    {
        return $this->isCaregiver()
            && $user->caregiver->sharesBusinessWith($entity);
    }

    protected function checkOnRole(User $user, string $ability, User $entity)
    {
        if (in_array($entity->role_type, ['office_user', 'admin'])) {
            // TODO: Office users should eventually be able to be managed by chain administrators
            return $user->role_type === 'admin';
        }

        return $user->can($ability, $entity->role);
    }
}