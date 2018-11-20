<?php

namespace App\Policies;

use App\Timesheet;
use App\User;

/**
 * Class TimesheetPolicy
 * TODO: This policy needs to allow checks for caregivers as well as office users
 *
 * @package App\Policies
 */
class TimesheetPolicy extends BasePolicy
{
    public function create(User $user, $data)
    {
        $timesheet = new Timesheet($data);
        return $timesheet->business_id == $timesheet->client->business_id
            && $user->can('read', $timesheet->client)
            && $user->can('read', $timesheet->caregiver);
    }

    public function read(User $user, Timesheet $timesheet)
    {
        return $this->businessCheck($user, $timesheet);
    }

    public function update(User $user, Timesheet $timesheet)
    {
        return $this->businessCheck($user, $timesheet);
    }

    public function delete(User $user, Timesheet $timesheet)
    {
        return $this->businessCheck($user, $timesheet);
    }
}
