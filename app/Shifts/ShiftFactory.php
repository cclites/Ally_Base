<?php
namespace App\Shifts;

use App\Billing\Invoiceable\ShiftService;
use App\Billing\Payer;
use App\Billing\ScheduleService;
use App\Billing\Service;
use App\Caregiver;
use App\Client;
use App\Schedule;
use App\Data\ScheduledRates;
use App\Shift;
use App\Shifts\Contracts\ShiftDataInterface;
use App\Shifts\Data\ClockData;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Class ShiftFactory
 *
 * @package App\Shifts\Data
 */
class ShiftFactory implements Arrayable
{
    protected $attributes = [];
    protected $services = [];
    protected $activities = [];

    protected function __construct(array $attributes) {
        $this->attributes = $attributes;
    }

    /**
     * Instantiate the factory without a related schedule
     *
     * @param \App\Client $client
     * @param \App\Caregiver $caregiver
     * @param \App\Shifts\Data\ClockData $clockIn
     * @param \App\Shifts\Data\ClockData|null $clockOut
     * @param \App\Data\ScheduledRates|null $rates
     * @param string|null $currentStatus
     * @param \App\Billing\Service|null $service
     * @param \App\Billing\Payer|null $payer
     * @param int $quickbooksService
     * @return \App\Shifts\ShiftFactory
     */
    public static function withoutSchedule(
        Client $client,
        Caregiver $caregiver,
        ClockData $clockIn,
        ?ClockData $clockOut = null,
        ?ScheduledRates $rates = null,
        ?string $currentStatus = null,
        ?Service $service = null,
        ?Payer $payer = null,
        ?int $quickbooksService = null,
        ?string $admin_note = null,
        ?int $visit_edit_action_id = null,
        ?int $visit_edit_reason_id = null
    ): self
    {
        $rates = self::resolveRates(clone $clockIn, $rates, $client->id, $caregiver->id, $service->id ?? null, $payer->id ?? null);
        return new self([
            'business_id'       => $client->business_id,
            'caregiver_id'      => $caregiver->id,
            'client_id'         => $client->id,
            'service_id'        => $service ? $service->id : self::getDefaultServiceId($client),
            'payer_id'          => $payer->id ?? null,
            'checked_in_method' => $clockIn->method,
            'checked_in_time'   => $clockIn->time,
            'checked_out_method'=> $clockOut ? $clockOut->method : Shift::METHOD_UNKNOWN,
            'checked_out_time'  => $clockOut ? $clockOut->time : null,
            'hours_type'        => $rates->hoursType(),
            'fixed_rates'       => $rates->fixedRates(),
            'client_rate'       => $rates->clientRate(),
            'caregiver_rate'    => $rates->caregiverRate(),
            'status'            => $currentStatus ?? self::getDefaultStatus(!!$clockOut),
            'quickbooks_service_id' => $quickbooksService,
            'admin_note'            => $admin_note,
            'visit_edit_action_id'  => $visit_edit_action_id,
            'visit_edit_reason_id'  => $visit_edit_reason_id
        ]);
    }

    /**
     * Instantiate the factory with a related schedule
     *
     * @param \App\Schedule $schedule
     * @param \App\Shifts\Data\ClockData $clockIn
     * @param \App\Shifts\Data\ClockData|null $clockOut
     * @param string|null $currentStatus
     * @return \App\Shifts\ShiftFactory
     */
    public static function withSchedule(
        Schedule $schedule,
        ClockData $clockIn,
        ?ClockData $clockOut = null,
        ?string $currentStatus = null
    ): self
    {
        $rates = self::resolveRates(clone $clockIn, $schedule->getRates(), $schedule->client_id, $schedule->caregiver_id, $schedule->service_id, $schedule->payer_id);
        $self = new self([
            'schedule_id'       => $schedule->id,
            'business_id'       => $schedule->business_id,
            'caregiver_id'      => $schedule->caregiver_id,
            'client_id'         => $schedule->client_id,
            'service_id'        => $schedule->service_id,
            'payer_id'          => $schedule->payer_id,
            'checked_in_method' => $clockIn->method,
            'checked_in_time'   => $clockIn->time,
            'checked_out_method'=> $clockOut ? $clockOut->method : Shift::METHOD_UNKNOWN,
            'checked_out_time'  => $clockOut ? $clockOut->time : null,
            'hours_type'        => $rates->hoursType(),
            'fixed_rates'       => $rates->fixedRates(),
            'client_rate'       => $rates->clientRate(),
            'caregiver_rate'    => $rates->caregiverRate(),
            'status'            => $currentStatus ?? self::getDefaultStatus(!!$clockOut),
            'quickbooks_service_id' => $schedule->quickbooks_service_id,
        ]);

        if ($schedule->services->count()) {
            $self->withServices($schedule->services->map(function(ScheduleService $service) use ($schedule, $clockIn) {
                $serviceData = Arr::except($service->toArray(), ['id', 'schedule_id', 'updated_at', 'created_at']);
                $rates = self::resolveRates(clone $clockIn, $service->getRates(), $schedule->client_id, $schedule->caregiver_id, $service->service_id, $service->payer_id);
                $serviceData = array_merge($serviceData, [
                    'client_rate' => $rates->clientRate(),
                    'caregiver_rate' => $rates->caregiverRate(),
                    'hours_type' => $rates->hoursType(),
                ]);

                return new ShiftService($serviceData);
            })->toArray());
        }

        return $self;
    }

    /**
     * Return the default shift status
     *
     * @param bool $hasBeenClockedOut
     * @param int|null $businessId
     * @return string
     */
    public static function getDefaultStatus(bool $hasBeenClockedOut, ?int $businessId = null): string
    {
        return $hasBeenClockedOut ? Shift::WAITING_FOR_CONFIRMATION : Shift::CLOCKED_IN;
    }

    /**
     * Get the default service id for the related business chain
     *
     * @param \App\Client $client
     * @return int|null
     */
    public static function getDefaultServiceId(Client $client): ?int
    {
        return Service::getDefault($client->business->chain_id)->id ?? null;
    }

    /**
     * Include shift data objects
     *
     * @param \App\Shifts\Contracts\ShiftDataInterface ...$dataObjects
     * @return \App\Shifts\ShiftFactory
     */
    public function withData(ShiftDataInterface ...$dataObjects): self
    {
        foreach($dataObjects as $object) {
            $this->attributes = array_merge($this->attributes, $object->toArray());
        }

        return $this;
    }

    /**
     * @param \App\Billing\Invoiceable\ShiftService[] $services
     * @return \App\Shifts\ShiftFactory
     */
    public function withServices(array $services): self
    {
        $this->services = $services;

        return $this;
    }

    /**
     * Create and persist a Shift
     *
     * @param \App\Shifts\Contracts\ShiftDataInterface ...$dataObjects
     * @return \App\Shift
     */
    public function create(ShiftDataInterface ...$dataObjects): Shift
    {
        $shift = Shift::create($this->withData(...$dataObjects)->toArray());

        if (count($this->services)) {
            $shift->update(['service_id' => null, 'payer_id' => null]);
            $shift->syncServices($this->services);
        }

        return $shift;
    }

    /**
     * Create, but do not persist, the shift.  This does not attach any relations, like services.
     *
     * @param \App\Shifts\Contracts\ShiftDataInterface ...$dataObjects
     * @return \App\Shift
     */
    public function make(ShiftDataInterface ...$dataObjects): Shift
    {
        return new Shift($this->withData(...$dataObjects)->toArray());
    }

    /**
     * Resolve default rates from the rate factory if none are provided
     *
     * @param \App\Shifts\Data\ClockData $clockIn
     * @param \App\Data\ScheduledRates|null $rates
     * @param int $clientId
     * @param int|null $caregiverId
     * @param int|null $serviceId
     * @param int|null $payerId
     * @return \App\Data\ScheduledRates|null
     */
    public static function resolveRates(ClockData $clockIn, ?ScheduledRates $scheduledRates, int $clientId, ?int $caregiverId, ?int $serviceId, ?int $payerId): ?ScheduledRates
    {
        if (!$scheduledRates || $scheduledRates->clientRate() === null) {
            $rateFactory = app(RateFactory::class);
            $client = Client::findOrFail($clientId);
            $timezone = $client->getTimezone();
            $effectiveDate = $clockIn->time->copy()->setTimezone($timezone ?? 'UTC')->toDateString();
            
            $rates = $rateFactory->findMatchingRate(
                $client,
                $effectiveDate,
                $scheduledRates ? $scheduledRates->fixedRates() : false,
                $serviceId,
                $payerId,
                $caregiverId
            );

            // For shifts that are unscheduled, we check if the hourly rate assigned
            // to the caregiver is 0, in which case we check if they have a fixed rate
            // assigned.  If there are no fixed rates either, we default to 0.
            if ($scheduledRates == null && $rates->caregiver_rate == 0 && $rates->client_rate == 0) {
                $fixedRates = $rateFactory->findMatchingRate(
                    $client,
                    $effectiveDate,
                    true,
                    $serviceId,
                    $payerId,
                    $caregiverId
                );
                if ($fixedRates->caregiver_rate > 0 && $fixedRates->client_rate > 0) {
                    $rates = $fixedRates;
                }
            }

            if ($scheduledRates) {
                $payer = $payerId ? Payer::find($payerId) : null;
                if ($scheduledRates->hoursType() == 'overtime') {
                    $rates = $rateFactory->getOvertimeRates($rates, $client, $payer);
                } else if ($scheduledRates->hoursType() == 'holiday') {
                    $rates = $rateFactory->getHolidayRates($rates, $client, $payer);
                }
            }
            
            return new ScheduledRates(
                $rates->client_rate ?? 0,
                $rates->caregiver_rate ?? 0,
                $rates->fixed_rates ?? false,
                $scheduledRates ? $scheduledRates->hoursType() : 'default'
            );
        }

        return $scheduledRates;
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->attributes;
    }
}