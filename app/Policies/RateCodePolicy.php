<?php

namespace App\Policies;

use App\RateCode;
use App\User;

class RateCodePolicy extends BasePolicy
{
    public function create(User $user, $data)
    {
        $code = new RateCode($data);
        return $this->businessCheck($user, $code);
    }

    public function read(User $user, RateCode $code)
    {
        return $this->businessCheck($user, $code);
    }

    public function update(User $user, RateCode $code)
    {
        return $this->businessCheck($user, $code);
    }

    public function delete(User $user, RateCode $code)
    {
        return $this->businessCheck($user, $code);
    }
}
