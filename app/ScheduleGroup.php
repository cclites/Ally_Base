<?php
namespace App;

use App\Scheduling\RuleParser;

class ScheduleGroup extends AuditableModel
{
    protected $table = 'schedule_groups';
    protected $guarded = ['id'];
    protected $dates = ['starts_at'];

    ////////////////////////////////////
    //// Relationship Methods
    ////////////////////////////////////

    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'group_id');
    }

    ////////////////////////////////////
    //// Instance Method
    ////////////////////////////////////

    function getRuleText()
    {
        return str_replace(", forever", "", RuleParser::create($this->starts_at, $this->rrule)->humanReadable());
    }

    function getStatistics(?string $timezone = null, string $futureFrom = 'now')
    {
        if (!$timezone) {
            $timezone = optional($this->schedules()->first()->client ?? null)->getTimezone() ?? 'America/New_York';
        }

        return [
            'id' => $this->id,
            'interval_type' => $this->interval_type,
            'rule_text' => $this->getRuleText(),
            'total_schedules' => $this->schedules()->count(),
            'future_schedules' => $this->schedules()->future($timezone, $futureFrom)->count(),
            'total_schedules_by_weekday' => $this->schedules()
                ->select('weekday', \DB::raw('count(*)'))
                ->groupBy('weekday')
                ->pluck('count(*)', 'weekday'),
            'future_schedules_by_weekday' => $this->schedules()->future($timezone, $futureFrom)
                ->select('weekday', \DB::raw('count(*)'))
                ->groupBy('weekday')
                ->pluck('count(*)', 'weekday'),
        ];
    }
}