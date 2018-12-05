<?php

namespace App\Reports;

use App\Shift;
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
     * Flag to include all confirmed statuses as well as unconfirmed, but not charged
     *
     * @var bool
     */
    protected $include_confirmed;

    /**
     * Flag to idenfiy that the report is being build
     * for the email report.
     *
     * @var bool
     */
    protected $for_email;

    /**
     * Optionally mask the caregiver names in the report.
     *
     * @var bool
     */
    protected $mask_names;

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
     * Turn on name masking.
     *
     * @return UnconfirmedShiftsReport
     */
    public function maskNames()
    {
        $this->mask_names = true;

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
     * Set include clocked in flag.
     *
     * @return UnconfirmedShiftsReport
     */
    public function includeConfirmed()
    {
        $this->include_confirmed = true;

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
        return Business::where('shift_confirmation_email', true)
            ->get()
            ->pluck('id')
            ->toArray();
    }

    /**
     * Get the applicable statuses according to the report settings.
     *
     * @return array
     */
    protected function getShiftStatuses()
    {
        $statuses = $this->include_confirmed ? ShiftStatusManager::getPendingStatuses()
            : ShiftStatusManager::getUnconfirmedStatuses();

        if (!$this->include_clocked_in) {
            $statuses = array_filter($statuses, function($status) {
                return $status !== 'CLOCKED_IN';
            });
        }

        return $statuses;
    }

    /**
     * Return the collection of rows matching report criteria
     *
     * @return \Illuminate\Support\Collection
     */
    protected function results()
    {
        $query = $this->query();
        if ($this->for_email) {
            $query = $query->forBusinesses($this->getIncludedBusinessIds());
        }

        return $query
            ->forClient($this->client)
            ->whereIn('status', $this->getShiftStatuses())
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
            ->map(function (Shift $s) {
                $total = floatval($s->hours) * floatval($s->caregiver_rate);

                return (object) [
                    'id' => $s->id,
                    'date' => $s->checked_in_time,
                    'client_id' => $s->client_id,
                    'client' => $s->client,
                    'business_name' => $s->business->name,
                    'caregiver' => $this->mask_names ? $s->caregiver->user->maskedName : $s->caregiver->user->name,
                    'hours' => $s->hours,
                    'confirmed' => $s->statusManager()->isConfirmed(),
                    'rate' => $s->costs()->getTotalHourlyCost(),
                    'total' => $s->costs()->getTotalCost(),
                ];
            });
    }
}
