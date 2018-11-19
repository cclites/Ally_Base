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
    //// User checks
    ////////////////////////////////////

    protected function isAdmin() {
        return auth()->user()->role_type === 'admin';
    }

    protected function isOfficeUser() {
        return auth()->user()->role_type === 'office_user';
    }

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

        if ($this->isOfficeUser() && $user->officeUser->chain_id == $entity->chain_id) {
            return true;
        }

        return false;
    }
}