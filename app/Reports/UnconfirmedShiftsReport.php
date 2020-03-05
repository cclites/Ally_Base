<?php

namespace App\Reports;

use App\Shift;
use App\Shifts\ShiftStatusManager;

class UnconfirmedShiftsReport extends BaseReport
{
    /**
     * @var int
     */
    protected $clients;

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
     * Flag to include shifts currently in progress.
     *
     * @var bool
     */
    protected $include_in_progress;

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
        $this->query = Shift::with(['client', 'caregiver', 'business', 'business.chain', 'client.defaultPayment', 'client.backupPayment', 'costHistory'])
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
     * Filter the results for only the given business.
     *
     * @param mixed business_id
     * @return self
     */
    public function forBusinesses($business_ids)
    {
        if (is_array($business_ids)) {
            $this->businesses = $business_ids;
        } else {
            $this->businesses = [$business_ids];
        }

        return $this;
    }

    /**
     * Filter the results for only the given client.
     *
     * @param int $client_id
     * @return UnconfirmedShiftsReport
     */
    public function forClient($client_id)
    {
        $this->clients = [$client_id];

        return $this;
    }

    /**
     * Filter the results for only the given client.
     *
     * @param array $client_ids
     * @return UnconfirmedShiftsReport
     */
    public function forClients(array $client_ids)
    {
        $this->clients = $client_ids;

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
     * Set include in progress flag.
     *
     * @return self
     */
    public function includeInProgress()
    {
        $this->include_in_progress = true;

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
        if (! empty($this->businesses)) {
            $query = $query->forBusinesses($this->businesses);
        }

        return $query
            ->forClients($this->clients)
            ->whereIn('status', $this->getShiftStatuses())
            ->get()
            ->filter(function (Shift $s) {
                if ($this->include_in_progress) {
                    if ($s->status == Shift::CLOCKED_IN && ! $s->business->sce_shifts_in_progress) {
                        // if business does not choose to include in progress shifts, skip
                        return false;
                    }
                }
                return true;
            })
            ->map(function (Shift $s) {
                $total = floatval($s->hours) * floatval($s->caregiver_rate);

                $costs = $s->costs();

                return (object) [
                    'id' => $s->id,
                    'date' => $s->checked_in_time,
                    'client_id' => $s->client_id,
                    'client' => $s->client,
                    'business_name' => $s->business->chain->name ?? $s->business->name ?? 'Caregiver Service',
                    'caregiver' => $this->mask_names ? $s->caregiver->user->maskedName : $s->caregiver->user->name,
                    'hours' => $s->duration,
                    'confirmed' => $s->statusManager()->isConfirmed(),
                    'rate' => $s->fixed_rates ? '---' : $costs->getTotalHourlyCost(),
                    'total' => $costs->getTotalCost(),
                ];
            });
    }
}
