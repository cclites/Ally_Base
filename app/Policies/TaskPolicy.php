<?php

namespace App\Policies;

use App\Task;
use App\User;

class TaskPolicy extends BasePolicy
{
    public function create(User $user, $data)
    {
        $task = new Task($data);
        return $this->businessCheck($user, $task);
    }

    public function read(User $user, Task $task)
    {
        return ($user->id == $task->assigned_user_id) 
            || $this->businessCheck($user, $task);
    }

    public function update(User $user, Task $task)
    {
        if ($user->active == 0) {
            return false;
        }

        return ($user->id == $task->assigned_user_id) 
            || $this->businessCheck($user, $task);
    }

    public function delete(User $user, Task $task)
    {
        return $this->businessCheck($user, $task);
    }
}
