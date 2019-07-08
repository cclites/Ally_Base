<?php

namespace App\Reports;

use App\Billing\GatewayTransaction;
use App\Billing\Invoiceable\ShiftService;
use App\Billing\Payer;
use App\Billing\Payments\Methods\BankAccount;
use App\Billing\Payments\Methods\CreditCard;
use App\Billing\Queries\OfflineClientInvoiceQuery;
use App\Business;
use App\Shift;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ShiftHistoryReport extends BusinessResourceReport
{
    /**
     * @var string
     */
    public $timezone = 'America/New_York';

    /**
     * BusinessOfflineArAgingReport constructor.
     */
    public function __construct()
    {
        $this->query = Shift::with([
            'business',
            'caregiver',
            'client',
            'shiftFlags',
            'statusHistory',
            'costHistory',
            'service',
            'services',

            // TODO: Need to clean this up.  This is all required to properly
            // get the rates, and we still have n+1 issues with
            // credit_card and bank_accounts being loaded.
            'client.primaryPayer',
            'client.primaryPayer.payer',
            'client.primaryPayer.client',
            'client.primaryPayer.client.business',
            'client.primaryPayer.paymentMethod',
        ]);
    }

    /**
     * Return the instance of the query builder for additional manipulation
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query() : \Illuminate\Database\Eloquent\Builder
    {
        return $this->query;
    }

    /**
     * Set the timezone of the report.
     *
     * @param string $timezone
     * @return ShiftHistoryReport
     */
    public function setTimezone(string $timezone) : self
    {
        $this->timezone = $timezone;

        return $this;
    }

    /**
     * Return the collection of rows matching report criteria
     *
     * @return \Illuminate\Support\Collection
     */
    protected function results() : ?iterable
    {
        return $this->query->get()->map(function (Shift $shift) {
            $hourlyRates = $shift->costs()->getHourlyRates();
            $totalRates = $shift->costs()->getTotalRates();
            return [
                'id' => $shift->id,
                'checked_in_time' => $shift->checked_in_time->format('c'),
                'checked_out_time' => optional($shift->checked_out_time)->format('c'),
                'hours' => $shift->duration(),
                'client_id' => $shift->client_id,
                'business_id' => $shift->business_id,
                'client_name' => optional($shift->client)->nameLastFirst(),
                'caregiver_id' => $shift->caregiver_id,
                'caregiver_name' => optional($shift->caregiver)->nameLastFirst(),
                'fixed_rates' => $shift->fixed_rates,
                'caregiver_rate' => number_format($hourlyRates->caregiver_rate, 2),
                'provider_fee' => number_format($hourlyRates->provider_fee, 2),
                'ally_fee' => number_format($hourlyRates->ally_fee, 2),
                'hourly_total' => number_format($hourlyRates->client_rate, 2),
                'other_expenses' => number_format($shift->other_expenses, 2),
                'mileage' => number_format($shift->mileage, 2),
                'mileage_costs' => number_format($shift->costs()->getMileageCost(), 2),
                'caregiver_total' => number_format($totalRates->caregiver_rate, 2),
                'provider_total' => number_format($totalRates->provider_fee, 2),
                'ally_total' => number_format($totalRates->ally_fee, 2),
                'ally_pct' => $shift->getAllyPercentage(),
                'shift_total' => number_format($totalRates->client_rate, 2),
                'hours_type' => $shift->hours_type,
                'confirmed' => $shift->statusManager()->isConfirmed(),
                'confirmed_at' => $shift->confirmed_at,
                'client_confirmed' => $shift->client_confirmed,
                'charged' => !($shift->statusManager()->isPending()),
                'charged_at' => $shift->charged_at,
                'status' => $shift->status ? title_case(preg_replace('/_/', ' ', $shift->status)) : '',
                // Send both verified and EVV for backwards compatibility
                'verified' => $shift->verified,
                'EVV' => ($shift->checked_in_verified && $shift->checked_out_verified),
                'flags' => $shift->flags,
                'created_at' => optional($shift->created_at)->toDateTimeString(),
                'services' => $this->mapServices($shift),
            ];
        })->sortBy('checked_in_time')
            ->values();
    }

    private function mapServices(Shift $shift) : ?iterable
    {
        if ($shift->service) {
//            return [
//                'id' => $shift->service->id,
//                'code' => $shift->service->code,
//                'name' => $shift->service->name,
//                'duration' => $shift->duration,
//            ];
            return [$shift->service->code . '-' . Str::limit($shift->service->name, 8) . '(' . $shift->duration . ')'];
        } else if ($shift->services->count()) {
            return $shift->services->map(function (ShiftService $shiftService) {
//                return [
//                    'id' => $shiftService->service->id,
//                    'code' => $shiftService->service->code,
//                    'name' => $shiftService->service->name,
//                    'duration' => $shiftService->duration,
//                ];
                return $shiftService->service->code . '-' . Str::limit($shiftService->service->name, 8) . '(' . $shiftService->duration . ')';
            });
        }

        return null;
    }

    /**
     * Apply filters to report.
     *
     * @param string $startDate
     * @param string $endDate
     * @param string|null $importId
     * @param int|null $clientId
     * @param int|null $caregiverId
     * @param string|null $paymentMethod
     * @param string|null $status
     * @param string|null $confirmed
     * @param string|null $clientType
     * @param string|null $flagType
     * @param array|null $flags
     * @param string|null $serviceId
     * @return ShiftHistoryReport
     */
    public function applyFilters(string $startDate, string $endDate, ?string $importId, ?int $clientId, ?int $caregiverId, ?string $paymentMethod, ?string $status, ?string $confirmed, ?string $clientType, ?string $flagType, ?array $flags, ?string $serviceId) : self
    {
        // Restrict businesses
        $this->query->forRequestedBusinesses();

        $start = (new Carbon($startDate . ' 00:00:00', $this->timezone))->setTimezone('UTC');
        $end = (new Carbon($endDate . ' 23:59:59', $this->timezone))->setTimezone('UTC');
        $this->query->whereBetween('checked_in_time', [$start, $end]);

        if (filled($clientId)) {
            $this->query->where('client_id', $clientId);
        }

        if (filled($caregiverId)) {
            $this->query->where('caregiver_id', $caregiverId);
        }

        if (filled($paymentMethod)) {
            $methodClass = null;
            switch($paymentMethod) {
                case 'credit_card':
                    $methodClass = CreditCard::class;
                    break;
                case 'bank_account':
                    $methodClass = BankAccount::class;
                    break;
                case 'business':
                    $methodClass = Business::class;
                    break;
            }
            if ($methodClass) {
                $method_type = maps_from_class($methodClass) ?? $methodClass;
                $this->query->whereHas('client', function($q) use ($methodClass, $method_type) {
                    $q->where('default_payment_type', $method_type);
                    if ($methodClass === Business::class) {
                        $q->orWhereHas('primaryPayer', function($q) {
                            $q->where('payer_id', '!=', Payer::PRIVATE_PAY_ID);
                        });
                    }
                });
                return $this;
            }
        }

        if (filled($importId)) {
            $this->query->where('import_id', $importId);
        }

        if (filled($status)) {
            if ($status === 'charged') {
                $this->query->whereReadOnly();
            } elseif ($status === 'uncharged') {
                $this->query->wherePending();
            }
        }

        if (filled($confirmed)) {
            if ($confirmed === 'unconfirmed') {
                $this->query->where('status', Shift::WAITING_FOR_CONFIRMATION);
            }
            else {
                // confirmed statuses
                $this->query->whereNotIn('status',  [Shift::WAITING_FOR_CONFIRMATION, Shift::CLOCKED_IN]);
            }
        }

        if (filled($clientType)) {
            $this->query->whereHas('client', function($query) use ($clientType) {
                $query->where('client_type', $clientType);
            });
        }

        if (filled($flagType)) {
            if ($flagType && $flagType !== 'any') {
                if ($flagType === 'none') {
                    $this->query->doesntHave('shiftFlags');
                }
                else if (is_array($flags) && count($flags) > 0) {
                    $this->query->whereFlagsIn($flags);
                }
            }
        }

        if (filled($serviceId)) {
            $this->query->where(function ($q) use ($serviceId) {
                $q->where('service_id', $serviceId)
                    ->orWhereHas('services', function ($q2) use ($serviceId) {
                        $q2->where('service_id', $serviceId);
                    });
            });
        }

        return $this;
    }
}
