<?php

namespace App\Policies;

use App\SmsThread;
use App\User;

class SmsThreadPolicy extends BasePolicy
{
    public function create(User $user, $data)
    {
        $thread = new SmsThread($data);
        return $this->businessCheck($user, $thread);
    }

    public function read(User $user, SmsThread $thread)
    {
        return $this->businessCheck($user, $thread);
    }

    public function update(User $user, SmsThread $thread)
    {
        return $this->businessCheck($user, $thread);
    }

    public function delete(User $user, SmsThread $thread)
    {
        return $this->businessCheck($user, $thread);
    }
}
