<?php
namespace App\Users;

use App\OfficeUser;
use App\Scheduling\Data\CalendarCaregiverFilter;
use App\Scheduling\Data\CalendarNextDayThreshold;
use App\Scheduling\Data\CalendarView;
use App\User;

class SettingsRepository
{
    /**
     * @param null|\App\User $user
     * @return \App\Users\OfficeUserSettings
     */
    public function getOfficeUserSettings(?User $user)
    {
        if (!$user || !$officeUser = $user->officeUser) {
            return new OfficeUserSettings();
        }
        $chain = $officeUser->businessChain;
        $defaultBusiness = $officeUser->getDefaultBusiness();

        return new OfficeUserSettings(
            (bool) $chain->scheduling,
            (bool) $chain->enable_schedule_groups,
            (bool) $defaultBusiness->ask_on_confirm,
            (bool) $defaultBusiness->calendar_remember_filters,
            $defaultBusiness->calendar_default_view ? new CalendarView($defaultBusiness->calendar_default_view) : null,
            $defaultBusiness->calendar_caregiver_filter ? new CalendarCaregiverFilter($defaultBusiness->calendar_caregiver_filter) : null,
            $defaultBusiness->calendar_next_day_threshold ? new CalendarNextDayThreshold($defaultBusiness->calendar_next_day_threshold) : null,
            $defaultBusiness->id,
            $chain->open_shifts_setting,
            $chain->id,
            $chain->calendar_week_start
        );
    }
}