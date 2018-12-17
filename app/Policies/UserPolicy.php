<?php
namespace App\Policies;

use App\User;

class UserPolicy extends BasePolicy
{
    public function create(User $user, $data)
    {
        // No user should be created directly
        return false;
    }

    public function read(User $user, User $entity)
    {
        return $user->id == $entity->id
            || $this->checkOnRole($user, 'read', $entity);
    }

    public function update(User $user, User $entity)
    {
        if ($user->active == 0) {
            return false;
        }

        return $user->id == $entity->id
            || $this->checkOnRole($user, 'update', $entity);
    }

    public function delete(User $user, User $entity)
    {
        if ($user->active == 0) {
            return false;
        }
        
        return $this->checkOnRole($user, 'delete', $entity);
    }
}
