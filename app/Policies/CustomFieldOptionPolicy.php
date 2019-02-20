<?php

namespace App\Policies;

use App\User;
use App\CustomFieldOption;

class CustomFieldOptionPolicy extends BasePolicy
{
    public function create(User $user, $data)
    {
        $option = new CustomFieldOption($data);
        return $this-businessCheckChain($user, $option->field);
    }

    public function read(User $user, CustomFieldOption $option)
    {
        return $this-businessCheckChain($user, $option->field);
    }

    public function update(User $user, CustomFieldOption $option)
    {
        return $this-businessCheckChain($user, $option->field);
    }

    public function delete(User $user, CustomFieldOption $option)
    {
        return $this-businessCheckChain($user, $option->field);
    }
}
