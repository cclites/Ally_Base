<?php
namespace App\Scheduling;

use App\Schedule;
use App\ScheduleGroup;
use App\ScheduleNote;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ScheduleEditor
{
    /**
     * @var \App\Scheduling\RuleGenerator
     */
    protected $ruleGenerator;

    public function __construct(RuleGenerator $ruleGenerator = null)
    {
        $this->ruleGenerator = $ruleGenerator ?: app(RuleGenerator::class);
    }

    /**
     * Update a single schedule
     *
     * @param \App\Schedule $schedule
     * @param array $data
     * @param string|null $notes
     * @param array $services
     * @return bool
     * @throws \Exception
     */
    public function updateSingle(Schedule $schedule, array $data, ?string $notes, array $services = []): bool
    {
        \DB::beginTransaction();

        // Verify date format
        if (isset($data['starts_at'])) {
            $data['starts_at'] = Carbon::parse($data['starts_at'])->toDateTimeString();
        }

        if ($schedule->update($data + ['group_id' => null])) {
            if ($schedule->notes != $notes) {
                if (strlen($notes)) {
                    $note = ScheduleNote::create(['note' => $notes]);
                    $schedule->attachNote($note);
                } else {
                    $schedule->deleteNote();
                }
            }

            $schedule->syncServices($services);
            \DB::commit();
            return true;
        }

        \DB::rollBack();
        return false;
    }

    /**
     * Update an entire schedule group
     *
     * @param \App\ScheduleGroup $group
     * @param \App\Schedule $startingSchedule
     * @param array $data
     * @param string|null $notes
     * @param array $services
     * @param int|null $weekdayOnly
     * @return \Illuminate\Support\Collection
     */
    public function updateGroup(ScheduleGroup $group, Schedule $startingSchedule, array $data, ?string $notes = null,
        array $services = [], ?int $weekdayOnly = null): Collection
    {
        if ($weekdayOnly !== null && $this->hasSchedulesOnOtherDays($group, $weekdayOnly)) {
            $newGroup = $this->createNewGroup($group, null, $weekdayOnly);
        }

        $scheduleQuery = $group->schedules();
        if ($weekdayOnly !== null) {
            $scheduleQuery->where('weekday', $weekdayOnly);
        }

        $schedules = $scheduleQuery->get();
        return $this->updateSchedules($schedules, $newGroup ?? $group, $startingSchedule, $data, $notes, $services);
    }

    /**
     * Update only future schedules from the specified starting point in a group
     *
     * @param \App\ScheduleGroup $group
     * @param \App\Schedule $startingSchedule
     * @param array $data
     * @param string|null $notes
     * @param array $services
     * @param int|null $weekdayOnly
     * @return \Illuminate\Support\Collection
     */
    public function updateFuture(ScheduleGroup $group, Schedule $startingSchedule, array $data, ?string $notes = null,
        array $services = [], ?int $weekdayOnly = null): Collection
    {
        if (!$this->hasPastSchedules($group, $startingSchedule)) {
            // Avoid unnecessary fork of group if there are no past schedules
            return $this->updateGroup($group, $startingSchedule, $data, $notes, $services, $weekdayOnly);
        }

        $newGroup = $this->createNewGroup($group, $startingSchedule, $weekdayOnly);
        $scheduleQuery = $group->schedules()->where('starts_at', '>=', $startingSchedule->starts_at);
        if ($weekdayOnly !== null) {
            $scheduleQuery->where('weekday', $weekdayOnly);
        }

        $schedules = $scheduleQuery->get();
        return $this->updateSchedules($schedules, $newGroup, $startingSchedule, $data, $notes, $services);
    }

    public function createNewGroup(ScheduleGroup $originalGroup, ?Schedule $startingSchedule, ?int $weekdayOnly = null): ScheduleGroup
    {
        $startsAt = $startingSchedule->starts_at ?? $originalGroup->starts_at;
        $this->ruleGenerator->startDate($startsAt)
            ->rrule($originalGroup->rrule);

        if (!in_array($this->ruleGenerator->getIntervalType(), [RuleGenerator::INTERVAL_WEEKLY, RuleGenerator::INTERVAL_BIWEEKLY])
            && $weekdayOnly !== null) {
            $this->ruleGenerator->setWeekdays($weekdayOnly);
        }

        $group = ScheduleGroup::create([
            'starts_at' => $startsAt,
            'end_date' => $originalGroup->end_date,
            'rrule' => $this->ruleGenerator->getRule(),
            'interval_type' => $originalGroup->interval_type,
        ]);

        return $group;
    }


    private function updateSchedules(Collection $schedules, ScheduleGroup $group, Schedule $startingSchedule,
        array $data, ?string $notes = null, array $services = []): Collection
    {
        // Prepend group id
        $data = ['group_id' => $group->id] + $data;

        \DB::beginTransaction();
        foreach($schedules as $schedule) {
            /** @var Schedule $schedule */
            // Prepend time difference to each schedule instance
            $data = [
                'starts_at' => $this->getNewStartsAt($schedule, $startingSchedule, $data['starts_at'] ?? null)
                ] + $data;
            $this->updateSingle($schedule, $data, $notes, $services);
        }
        \DB::commit();

        return $schedules;
    }

    private function hasSchedulesOnOtherDays(ScheduleGroup $group, int $weekdayOnly): bool
    {
        return $group->schedules()
            ->where('weekday', '!=', $weekdayOnly)
            ->exists();
    }

    private function hasPastSchedules(ScheduleGroup $group, Schedule $startingSchedule): bool
    {
        return $group->schedules()
            ->where('starts_at', '<', $startingSchedule->starts_at)
            ->exists();
    }

    private function getNewStartsAt(Schedule $currentSchedule, Schedule $startingSchedule, ?string $startsAt): Carbon
    {
        if (!$startsAt) return $currentSchedule->starts_at;

        $startsAt = Carbon::parse($startsAt, $startingSchedule->starts_at->timezone);
        $diffInDays = $startingSchedule->starts_at->diffInDays($startsAt, false);
        $newStartsAt = $currentSchedule->starts_at->copy()->addDays($diffInDays)->setTimeFromTimeString($startsAt->toTimeString());

        return $newStartsAt;
    }
}