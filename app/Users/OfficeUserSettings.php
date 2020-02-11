<?php
namespace App\Users;

use App\Data\Traits\ReflectsToArray;
use App\Scheduling\Data\CalendarCaregiverFilter;
use App\Scheduling\Data\CalendarNextDayThreshold;
use App\Scheduling\Data\CalendarView;
use JsonSerializable;

class OfficeUserSettings implements JsonSerializable
{
    use ReflectsToArray;

    private $scheduling;
    private $enable_schedule_groups;
    private $calendar_default_view;
    private $calendar_caregiver_filter;
    private $calendar_remember_filters;
    private $calendar_next_day_threshold;
    private $ask_on_confirm;
    private $default_business_id;
    private $open_shifts_setting;

    public function __construct(
        bool $scheduling = true,
        bool $enable_schedule_groups = true,
        bool $ask_on_confirm = true,
        bool $calendar_remember_filters = true,
        ?CalendarView $calendar_default_view = null,
        ?CalendarCaregiverFilter $calendar_caregiver_filter = null,
        ?CalendarNextDayThreshold $calendar_next_day_threshold = null,
        int $default_business_id = null,
        string $open_shifts_setting = null
    )
    {
        $this->scheduling = $scheduling;
        $this->enable_schedule_groups = $enable_schedule_groups;
        $this->ask_on_confirm = $ask_on_confirm;
        $this->calendar_remember_filters = $calendar_remember_filters;
        $this->calendar_default_view = $calendar_default_view ?? CalendarView::TIMELINE_WEEK();
        $this->calendar_caregiver_filter = $calendar_caregiver_filter ?? CalendarCaregiverFilter::UNASSIGNED();
        $this->calendar_next_day_threshold = $calendar_next_day_threshold ?? CalendarNextDayThreshold::DISABLED();
        $this->default_business_id = $default_business_id;
        $this->open_shifts_setting = $open_shifts_setting;
    }

    function scheduling(): bool
    {
        return $this->scheduling;
    }

    function enable_schedule_groups(): bool
    {
        return $this->enable_schedule_groups;
    }

    function calendar_default_view(): CalendarView
    {
        return $this->calendar_default_view;
    }

    function calendar_caregiver_filter(): CalendarCaregiverFilter
    {
        return $this->calendar_caregiver_filter;
    }

    function calendar_next_day_threshold(): CalendarNextDayThreshold
    {
        return $this->calendar_next_day_threshold;
    }

    function calendar_remember_filters(): bool
    {
        return $this->calendar_remember_filters;
    }

    function ask_on_confirm(): bool
    {
        return $this->ask_on_confirm;
    }

    function default_business_id(): int
    {
        return $this->default_business_id;
    }

    function open_shifts_setting(): string
    {
        return $this->open_shifts_setting;
    }
}