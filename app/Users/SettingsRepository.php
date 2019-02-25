<?php
namespace App\Users;

use App\OfficeUser;
use App\Scheduling\Data\CalendarCaregiverFilter;
use App\Scheduling\Data\CalendarNextDayThreshold;
use App\Scheduling\Data\CalendarView;

class SettingsRepository
{
    public function find(?OfficeUser $user = null)
    {
        if (!$user) {
            return new UserSettings();
        }

        $chain = $user->businessChain;
        $firstBusiness = $user->businesses()->first();

        return new UserSettings(
            (bool) $chain->scheduling,
            (bool) $chain->enable_schedule_groups,
            (bool) $firstBusiness->ask_on_confirm,
            (bool) $firstBusiness->calendar_remember_filters,
            $firstBusiness->calendar_default_view ? new CalendarView($firstBusiness->calendar_default_view) : null,
            $firstBusiness->calendar_caregiver_filter ? new CalendarCaregiverFilter($firstBusiness->calendar_caregiver_filter) : null,
            $firstBusiness->calendar_next_day_threshold ? new CalendarNextDayThreshold($firstBusiness->calendar_next_day_threshold) : null,
        );
    }
}