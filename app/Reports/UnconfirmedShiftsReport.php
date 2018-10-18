<?php

namespace App\Reports;

use App\Shift;
use Carbon\Carbon;
use App\Shifts\ShiftStatusManager;
use App\Business;

class UnconfirmedShiftsReport extends BaseReport
{
    /**
     * @var int
     */
    protected $client;

    /**
     * Flag to include CLOCKED_IN status as 'unconfirmed'
     *
     * @var bool
     */
    protected $include_clocked_in;

    /**
     * Flag to idenfiy that the report is being build
     * for the email report.
     *
     * @var bool
     */
    protected $for_email;

    /**
     *  Constructor.
     */
    public function __construct()
    {
        $this->query = Shift::with(['client', 'caregiver', 'business'])
            ->orderBy('checked_in_time', 'asc');
    }

    /**
     * Return the instance of the query builder for additional manipulation
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public function query()
    {
        return $this->query;
    }

    /**
     * Filter the results for only the given client.
     *
     * @param int $client_id
     * @return UnconfirmedShiftsReport
     */
    public function forClient($client_id)
    {
        $this->client = $client_id;

        return $this;
    }

    /**
     * Set for email flag
     *
     * @return UnconfirmedShiftsReport
     */
    public function forEmail()
    {
        $this->for_email = true;

        return $this;
    }

    /**
     * Set include clocked in flag.
     *
     * @return UnconfirmedShiftsReport
     */
    public function includeClockedIn()
    {
        $this->include_clocked_in = true;

        return $this;
    }

    /**
     * Get the businesses that are set up to send shift confirmation emails.
     * Returns an empty array if report is not for email.
     *
     * @return array
     */
    public function getIncludedBusinessIds()
    {
        if ($this->for_email) {
            return Business::where('shift_confirmation_email', true)
                ->get()
                ->pluck('id');
        }

        return [];
    }

    /**
     * Get the statuses classified as unconfirmed according to the report settings.
     *
     * @return array
     */
    protected function getUnconfirmedStatuses() 
    {
        return $this->include_clocked_in ? ShiftStatusManager::getUnconfirmedStatuses() : [Shift::WAITING_FOR_CONFIRMATION];
    }

    /**
     * Return the collection of rows matching report criteria
     *
     * @return \Illuminate\Support\Collection
     */
    protected function results()
    {
        return $this->query()
            ->forBusinesses($this->getIncludedBusinessIds())
            ->forClient($this->client)
            ->whereIn('status', $this->getUnconfirmedStatuses())
            ->get()
            ->filter(function (Shift $s) {
                if ($this->for_email) {
                    if ($s->status == Shift::CLOCKED_IN && ! $s->business->sce_shifts_in_progress) {
                        // if business does not choose to include in progress shifts, skip
                        return false;
                    }
                }
                return true;
            })
            ->map(function(Shift $s) {
                $total = floatval($s->hours) * floatval($s->caregiver_rate);

                return (object) [
                    'id' => $s->id,
                    'date' => $s->checked_in_time,
                    'client_id' => $s->client_id,
                    'client' => $s->client,
                    'business_name' => $s->business->name,
                    'caregiver' => $s->caregiver->user->maskedName,
                    'hours' => $s->hours,
                    'rate' => $s->costs()->getTotalHourlyCost(),
                    'total' => $s->costs()->getTotalCost(),
                ];
            });
    }
}