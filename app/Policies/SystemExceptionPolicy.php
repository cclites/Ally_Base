<?php

namespace App\Policies;

use App\SystemException;
use App\User;

class SystemExceptionPolicy extends BasePolicy
{
    public function create(User $user, $data)
    {
        $exception = new SystemException($data);
        return $this->businessCheck($user, $exception);
    }

    public function read(User $user, SystemException $exception)
    {
        return $this->businessCheck($user, $exception);
    }

    public function update(User $user, SystemException $exception)
    {
        return $this->businessCheck($user, $exception);
    }

    public function delete(User $user, SystemException $exception)
    {
        return $this->businessCheck($user, $exception);
    }
}
