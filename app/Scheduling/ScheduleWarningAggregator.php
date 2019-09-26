<?php

namespace App\Scheduling;

use App\CaregiverDayOff;
use App\Schedule;
use App\Shifts\ServiceAuthValidator;
use Carbon\Carbon;
use App\Client;

/**
 * Class ScheduleWarningAggregator
 * @package App\Scheduling
 */
class ScheduleWarningAggregator
{
    /**
     * @var Schedule
     */
    private $schedule;

    /**
     * @var \Illuminate\Support\Collection
     */
    private $warnings;

    /**
     * ScheduleWarningAggregator constructor.
     * @param Schedule $schedule
     */
    public function __construct(Schedule $schedule)
    {
        $this->schedule = $schedule;
        $this->warnings = collect([]);
    }

    /**
     * Run all warning checks and return the warnings
     * as an array of strings.
     *
     * @return array
     */
    public function getAll() : array
    {
        $this->warnings = collect([]);

        if (! empty($this->schedule->caregiver)) {
            $this->checkCaregiverScheduleConflicts();
            $this->checkPreferenceMismatches();
            $this->checkCaregiverRestrictions();
            $this->checkCaregiverDaysOff();
            $this->checkCaregiverLicenses();
        }

        $this->checkClientServiceAuths();

        return $this->warnings->toArray();
    }

    /**
     * 
     * the algorithm is as follows:
     * - grab all scheduled shifts for that cargiver on the specified day, I figure that parsing the times in PHP would be more efficient than creating a list of where clauses for multiple whole-table-searches
     * - check the array of discovered shifts for the following 3 conditions:
     *      - any shift that starts during the one being setup
     *      - any shift that ends during the one being setup
     *      - any shift that starts before this one, and ends after ( therefore this shift is a sub-set of the other one )
     * - if an offending shift is found, the date/time/client information will be returned as per the task spec
     * 
     * 
     * I cannot think of any scenarios to search for, if I missed something please let me know
     * 
     */
    public function checkCaregiverScheduleConflicts()
    {
        $conflicts = collect([]);

        /** @var \App\Caregiver $caregiver */
        $caregiver = $this->schedule->caregiver;

        // take the input date for when the scheduled shift is trying to be made at and find all scheduled shifts for that entire day
        $other_schedules = $caregiver->schedules()->whereBetween( 'starts_at', [ Carbon::parse( $this->schedule->starts_at )->startOfDay(), Carbon::parse( $this->schedule->starts_at )->endOfDay() ] )->get();

        if( count( $other_schedules ) < 1 ) return; // if nothing else is scheduled that day, return early

        $start = Carbon::parse( $this->schedule->starts_at );
        $end   = Carbon::parse( $this->schedule->starts_at )->addMinutes( $this->schedule->duration );

        $target_schedule = null;

        foreach( $other_schedules as $schedule ){

            $target_start = Carbon::parse( $schedule->starts_at );
            $target_end   = Carbon::parse( $schedule->starts_at )->addMinutes( $schedule->duration );

            if( $target_start->gte( $start ) && $target_start->lt( $end ) ){
                // if this shift starts during the one being created, grab it and break the loop

                $target_schedule = $schedule;
                break;
            }

            if( $target_end->gt( $start ) && $target_end->lte( $end ) ){
                // if this shift ends during the one being created, grab it and break the loop

                $target_schedule = $schedule;
                break;
            }

            if( $target_start->lt( $start ) && $target_end->gt( $end ) ){
                // if the shift being created is entirely within another shift, grab it and break the loop

                $target_schedule = $schedule;
                break;
            }
        }

        if( $target_schedule ){
            // if we found a conflicting schedule, grab it's relevant meta data
            // date/time/client information

            $target_start = Carbon::parse( $target_schedule->starts_at )->format( 'm/d/Y h:i:s A' );
            $target_end   = Carbon::parse( $target_schedule->starts_at )->addMinutes( $target_schedule->duration )->format( 'm/d/Y h:i:s A' );
            $client       = Client::find( $target_schedule->client_id );

            $conflicts->push( $caregiver->name . ' is currently scheduled at ' . $target_start . ' to ' . $target_end . ' with client ' . $client->name );
        }

        if ( $conflicts->count() ) {

            $this->pushWarnings( $conflicts, 'Caregiver Schedule Conflict' );
        }
    }

    public function checkPreferenceMismatches()
    {
        $mismatches = collect([]);

        /** @var \App\Caregiver $caregiver */
        $caregiver = $this->schedule->caregiver;

        /** @var \App\Client $client */
        $client = $this->schedule->client;
        if (empty($client)) {
            return;
        }

        /** @var \App\ClientPreferences $preferences */
        $preferences = $client->preferences;
        if (empty($preferences)) {
            return;
        }

        if ($preferences->smokes && ! $caregiver->smoking_okay) {
            $mismatches->push('Caregiver is not okay with smoking');
        }

        if ($preferences->pets_dogs && ! $caregiver->pets_dogs_okay) {
            $mismatches->push('Caregiver is not okay with dogs');
        }

        if ($preferences->pets_cats && ! $caregiver->pets_cats_okay) {
            $mismatches->push('Caregiver is not okay with cats');
        }

        if ($preferences->pets_birds && ! $caregiver->pets_birds_okay) {
            $mismatches->push('Caregiver is not okay with birds');
        }

        if ($preferences->gender == 'M' && $caregiver->gender != 'M') {
            $mismatches->push('Client prefers male caregivers');
        }

        if ($preferences->gender == 'F' && $caregiver->gender != 'F') {
            $mismatches->push('Client prefers female caregivers');
        }

        if ($preferences->license && $caregiver->certification != $preferences->license) {
            $mismatches->push('Client requires a ' . $preferences->license);
        }

        // TODO: add check for spoken language

        if ($mismatches->count()) {
            $this->pushWarnings([$mismatches->implode(', ')], 'Preferences Mismatch');
        }
    }

    /**
     * Check if the selected Caregiver has free text restrictions.
     *
     * @return bool
     */
    public function checkCaregiverRestrictions()
    {
        // check for any caregiver restrictions
        if (empty($this->schedule->caregiver->restrictions)) {
            return false;
        }

        $restrictions = $this->schedule->caregiver->restrictions->pluck('description');
        if ($restrictions->count() > 0) {
            $this->pushWarnings(
                [$restrictions->implode(', ')],
                'Caregiver Restrictions'
            );
        }

        return true;
    }

    /**
     * Check for expired/expiring caregiver licenses.
     *
     * @return bool
     */
    public function checkCaregiverLicenses()
    {
        // check for expired/expiring caregiver licenses
        $expired = $this->schedule->caregiver->licenses()
            ->whereApplicable()
            ->where('expires_at', '<', Carbon::now())
            ->get()
            ->map(function ($license) {
                $date = $license->expires_at->format('m/d/Y');
                return "{$this->schedule->caregiver->name}'s {$license->name} certification expired on $date.";
            });

        $expiring = $this->schedule->caregiver->licenses()
            ->whereBetween('expires_at', [Carbon::now(), Carbon::now()->addDays(30)])
            ->get()
            ->map(function ($license) {
                $date = $license->expires_at->format('m/d/Y');
                return "{$this->schedule->caregiver->name}'s {$license->name} certification expires on $date.";
            });

        if (empty($expired) && empty($expiring)) {
            return false;
        }

        $this->pushWarnings($expired);
        $this->pushWarnings($expiring);

        return true;
    }

    /**
     * Check if the Caregiver has marked the day off for any of
     * the dates during the scheduled shift.
     *
     * @return bool
     */
    public function checkCaregiverDaysOff()
    {
        $scheduleStart = $this->schedule->starts_at->format('Y-m-d');
        $scheduleEnd = $this->schedule->getEndDateTime()->format('Y-m-d');

        $warnings = $this->schedule->caregiver->daysOff()
                    ->where(function ($q) use($scheduleStart, $scheduleEnd) {
                        $q->where([
                            ['start_date', '<=', $scheduleStart],
                            ['end_date', '>=', $scheduleStart],
                        ])
                        ->orWhere([
                            ['start_date', '<=', $scheduleStart],
                            ['start_date', '>=', $scheduleEnd],
                        ])
                        ->orWhere([
                            ['start_date', $scheduleStart],
                            ['end_date', $scheduleEnd],
                        ]);
                    })
                    ->get()
                    ->map(function ($dayOff) {
                        $start_date = Carbon::parse($dayOff->start_date)->format('m/d/Y');
                        $end_date = Carbon::parse($dayOff->end_date)->format('m/d/Y');
                        return "{$this->schedule->caregiver->name} has marked themselves unavailable on $start_date to $end_date ({$dayOff->description}).";
                    });

        if (empty($warnings)) {
            return false;
        }

        $this->pushWarnings($warnings);

        return true;
    }

    /**
     * Check the schedule against any active ClientAuthorizations.
     *
     * @return bool
     */
    public function checkClientServiceAuths()
    {
        if (empty($this->schedule->client_id) ||
            empty($this->schedule->starts_at) ||
            empty($this->schedule->duration)) {
                return false;
        }

        $validator = new ServiceAuthValidator($this->schedule->client);

        if ($validator->scheduleExceedsMaxClientHours($this->schedule)) {
            $this->pushWarnings(["If scheduled, this shift would exceed the client's max weekly hours of {$this->schedule->client->max_weekly_hours}"]);
        }

        if ($auth = $validator->scheduleExceedsServiceAuthorization($this->schedule)) {
            $this->pushWarnings(["If scheduled, this shift would exceed service authorization code #{$auth->service_auth_id}"]);
        }

        return true;
    }

    /**
     * Append the warnings collection with the given data.
     *
     * @param iterable $warnings
     * @param string $label
     */
    public function pushWarnings(iterable $warnings, string $label = 'Warning') : void
    {
        foreach ($warnings as $warning) {
            $this->warnings->push([
                'description' => $warning,
                'label' => $label
            ]);
        }
    }
}
