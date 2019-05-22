<?php

namespace App\Policies;

use App\User;
use App\SystemNotification;

class SystemNotificationPolicy extends BasePolicy
{
    public function create(User $user, $data)
    {
        // only the system should create System Notifications
        return false;
    }

    public function read(User $user, SystemNotification $notification)
    {
        return $user->id == $notification->user_id;
    }

    public function update(User $user, SystemNotification $notification)
    {
        return $user->id == $notification->user_id;
    }

    public function delete(User $user, SystemNotification $notification)
    {
        return $user->id == $notification->user_id;
    }
}
