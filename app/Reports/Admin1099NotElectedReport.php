<?php

namespace App\Reports;

use App\CaregiverYearlyEarnings;
use App\Caregiver1099Payer;
use App\Caregiver1099;

class Admin1099NotElectedReport extends BaseReport
{
    /**
     * Admin1099NotElectedReport constructor.
     */
    public function __construct()
    {
        $this->query = CaregiverYearlyEarnings::with([
            'client',
            'client.user',
            'caregiver',
            'caregiver.user',
            'caregiver.phoneNumber',
            'business',
        ])
            ->whereHas('client', function ($q) {
                $q->where('send_1099', 'no');
            })
            ->overThreshold(Caregiver1099::THRESHOLD);
    }

    /**
     * Apply filters to report query.
     *
     * @param string $year
     * @param string|null $caregiverId
     * @param string|null $clientId
     * @param iterable|null $businesses
     * @param string|null $payer
     */
    public function applyFilters(string $year, ?string $caregiverId = null, ?string $clientId = null, ?iterable $businesses = null, ?string $payer = null)
    {
        $this->query->where('caregiver_yearly_earnings.year', $year)
            ->when($caregiverId, function ($q, $value) {
                $q->where('caregiver_yearly_earnings.caregiver_id', $value);
            })
            ->when($clientId, function ($q, $value) {
                $q->where('caregiver_yearly_earnings.client_id', $value);
            })
            ->when($businesses, function ($q, $value) {
                $q->whereIn('caregiver_yearly_earnings.business_id', $value);
            })
            ->when($payer == Caregiver1099Payer::CLIENT(), function ($q) {
                $q->whereUsesClientPayer();
            })
            ->when($payer == Caregiver1099Payer::ALLY(), function ($q) {
                $q->whereUsesAllyPayer();
            });
    }

    /**
     * Return the collection of rows matching report criteria
     *
     * @return \Illuminate\Support\Collection
     */
    public function results(): ?iterable
    {
        return $this->query->get()
            ->map(function (CaregiverYearlyEarnings $earnings) {
                return [
                    'client_id' => $earnings->client_id,
                    'client_name' => $earnings->client->last_name . ', ' . $earnings->client->first_name,
                    'caregiver_id' => $earnings->caregiver_id,
                    'caregiver_name' => $earnings->caregiver->last_name . ', ' . $earnings->caregiver->first_name,
                    'caregiver_email' => $earnings->caregiver->email,
                    'caregiver_phone' => optional($earnings->caregiver->phoneNumber)->number,
                    'business_id' => $earnings->business->id,
                    'business_name' => $earnings->business->name,
                    'earnings' => $earnings->earnings,
                    'year' => $earnings->year,
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
