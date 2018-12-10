<?php

namespace App\Policies;

use App\CaregiverApplication;
use App\User;

class CaregiverApplicationPolicy extends BasePolicy
{
    public function create(User $user, $data)
    {
        $application = new CaregiverApplication($data);
        return $this->businessChainCheck($user, $application);
    }

    public function read(User $user, CaregiverApplication $application)
    {
        return $this->businessChainCheck($user, $application);
    }

    public function update(User $user, CaregiverApplication $application)
    {
        return $this->businessChainCheck($user, $application);
    }

    public function delete(User $user, CaregiverApplication $application)
    {
        return $this->businessChainCheck($user, $application);
    }
}
