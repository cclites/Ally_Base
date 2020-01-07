<?php

namespace App\Reports;

use App\CaregiverYearlyEarnings;
use App\Caregiver1099Payer;
use App\Caregiver1099;

class Caregiver1099PreviewReport extends BaseReport
{
    /**
     * constructor.
     */
    public function __construct()
    {
        $this->query = CaregiverYearlyEarnings::with(['client', 'caregiver', 'business'])
            ->select(['caregiver_yearly_earnings.*', 'c1099.id as caregiver_1099_id'])
            ->leftJoin('caregiver_1099s as c1099', function ($join) {
                $join->on('caregiver_yearly_earnings.client_id', '=', 'c1099.client_id')
                    ->on('caregiver_yearly_earnings.caregiver_id', '=', 'c1099.caregiver_id')
                    ->on('caregiver_yearly_earnings.year', '=', 'c1099.year');
            })
            ->whereHas('client', function ($q) {
                $q->whereIn('caregiver_1099', [
                    Caregiver1099Payer::ALLY(),
                    Caregiver1099Payer::ALLY_LOCKED(),
                    Caregiver1099Payer::CLIENT()
                ])->where('send_1099', 'yes');
            })
            ->overThreshold(Caregiver1099::THRESHOLD);
    }

    public function applyFilters(string $year, ?string $caregiverId = null, ?string $clientId = null, ?string $businessId = null, ?string $payer = null, ?bool $created = null)
    {
        $this->query->where('caregiver_yearly_earnings.year', $year)
            ->when($caregiverId, function ($q, $value) {
                $q->where('caregiver_yearly_earnings.caregiver_id', $value);
            })
            ->when($clientId, function ($q, $value) {
                $q->where('caregiver_yearly_earnings.client_id', $value);
            })
            ->when($businessId, function ($q, $value) {
                $q->where('caregiver_yearly_earnings.business_id', $value);
            })
            ->when($payer == Caregiver1099Payer::CLIENT(), function ($q) {
                $q->whereUsesClientPayer();
            })
            ->when($payer == Caregiver1099Payer::ALLY(), function ($q) {
                $q->whereUsesAllyPayer();
            })
            ->when($created === false, function ($q) {
                $q->whereNull('c1099.id');
            })
            ->when($created === true, function ($q) {
                $q->whereNotNull('c1099.id');
            });

        // TODO: how does transmitted work??
//        if (array_key_exists('transmitted', $this->filters) && filled($this->filters['transmitted']['value'])) {
//            if ($this->filters['transmitted']['value'] && $this->filters['transmitted']['value'] === 1) {
//                $query->whereNotNull('ct.transmitted_at');
//            } elseif ($this->filters['transmitted']['value'] && $this->filters['transmitted']['value'] === 0) {
//                // TODO: check if this if statement is correct
//                $query->whereNull('ct.transmitted_at');
//            }
//        }

    }

    /**
     * Return the collection of rows matching report criteria
     *
     * @return \Illuminate\Support\Collection
     */
    public function results() : ?iterable
    {
        return $this->query->get()
            ->map(function (CaregiverYearlyEarnings $earnings) {
                return [
                    'client_id' => $earnings->client_id,
                    'client_first_name' => $earnings->client->first_name,
                    'client_last_name' => $earnings->client->last_name,
                    'caregiver_id' => $earnings->caregiver_id,
                    'caregiver_first_name' => $earnings->caregiver->first_name,
                    'caregiver_last_name' => $earnings->caregiver->last_name,
                    'business_id' => $earnings->business->id,
                    'business_name' => $earnings->business->name,
                    'payment_total' => $earnings->earnings,
                    'caregiver_1099' => $earnings->client->caregiver_1099,
                    'caregiver_1099_id' => $earnings->caregiver_1099_id,
                    'year' => $earnings->year,
                    'errors' => $earnings->getMissing1099Errors(),
                ];
            });
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
}
