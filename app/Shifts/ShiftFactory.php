<?php
namespace App\Shifts;

use App\Billing\Payer;
use App\Billing\ScheduleService;
use App\Billing\Service;
use App\Caregiver;
use App\Client;
use App\Schedule;
use App\Shift;
use App\Shifts\Contracts\ShiftDataInterface;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;

/**
 * Class ShiftFactory
 *
 * @package App\Shifts\Data
 */
class ShiftFactory implements Arrayable
{
    protected $attributes;
    protected $services;
    protected $activities;

    protected function __construct(array $attributes) {
        $this->attributes = $attributes;
    }

    public static function withoutSchedule(
        Client $client,
        Caregiver $caregiver,
        string $hoursType,
        bool $fixedRates,
        ?float $clientRate,
        ?float $caregiverRate,
        string $clockInMethod,
        Carbon $clockInTime,
        ?string $clockOutMethod = null,
        ?Carbon $clockOutTime = null,
        ?string $currentStatus = null,
        ?Service $service = null,
        ?Payer $payer = null
    ): self
    {
        return new self([
            'business_id'       => $client->business_id,
            'caregiver_id'      => $caregiver->id,
            'client_id'         => $client->id,
            'service_id'        => $service ? $service->id : self::getDefaultServiceId($client),
            'payer_id'          => $payer->id ?? null,
            'checked_in_method' => $clockInMethod,
            'checked_in_time'   => $clockInTime->setTimezone('UTC'),
            'checked_out_method'=> $clockOutMethod ?? $clockOutTime ? $clockInMethod : Shift::METHOD_UNKNOWN,
            'checked_out_time'  => $clockOutTime ? $clockOutTime->setTimezone('UTC') : null,
            'hours_type'        => $hoursType,
            'fixed_rates'       => $fixedRates,
            'client_rate'       => $clientRate,
            'caregiver_rate'    => $caregiverRate,
            'status'            => $currentStatus ?? self::getDefaultStatus(!!$clockOutTime),
        ]);
    }

    public static function withSchedule(
        Schedule $schedule,
        string $clockInMethod,
        Carbon $clockInTime,
        ?string $clockOutMethod = null,
        ?Carbon $clockOutTime = null,
        ?string $currentStatus = null
    ): self
    {
        $self = new self([
            'schedule_id'       => $schedule->id,
            'business_id'       => $schedule->business_id,
            'caregiver_id'      => $schedule->caregiver_id,
            'client_id'         => $schedule->client_id,
            'service_id'        => $schedule->service_id,
            'payer_id'          => $schedule->payer_id,
            'checked_in_method' => $clockInMethod,
            'checked_in_time'   => $clockInTime->setTimezone('UTC'),
            'checked_out_method'=> $clockOutMethod ?? $clockOutTime ? $clockInMethod : Shift::METHOD_UNKNOWN,
            'checked_out_time'  => $clockOutTime ? $clockOutTime->setTimezone('UTC') : null,
            'hours_type'        => $schedule->hours_type,
            'fixed_rates'       => $schedule->fixed_rates,
            'client_rate'       => $schedule->client_rate ?? 0.0,
            'caregiver_rate'    => $schedule->caregiver_rate ?? 0.0,
            'status'            => $currentStatus ?? self::getDefaultStatus(!!$clockOutTime),
        ]);

        if ($schedule->services->count()) {
            $self->withServices($schedule->services->map(function(ScheduleService $service) use ($schedule, $clockInTime) {
                $serviceData = array_except($service->toArray(), ['id', 'schedule_id', 'updated_at', 'created_at']);
                return $serviceData;
            }));
        }

        return $self;
    }

    public static function getDefaultStatus(bool $hasBeenClockedOut, ?int $businessId = null): string
    {
        return $hasBeenClockedOut ? Shift::WAITING_FOR_CONFIRMATION : Shift::CLOCKED_IN;
    }

    public static function getDefaultServiceId(Client $client): ?int
    {
        return Service::getDefault($client->business->chain_id)->id ?? null;
    }

    public function withData(ShiftDataInterface ...$dataObjects): self
    {
        foreach($dataObjects as $object) {
            $this->attributes = array_merge($this->attributes, $object->toArray());
        }

        return $this;
    }

    public function withServices(array $services): self
    {
        $this->services = $services;

        return $this;
    }

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
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->attributes;
    }
}