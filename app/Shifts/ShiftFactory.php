<?php
namespace App\Shifts;

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

    protected function __construct(array $attributes) {
        $this->attributes = $attributes;
    }

    public static function withoutSchedule(
        Client $client,
        Caregiver $caregiver,
        string $hoursType,
        bool $fixedRates,
        float $clientRate,
        float $caregiverRate,
        string $clockInMethod,
        Carbon $clockInTime,
        ?string $clockOutMethod = null,
        ?Carbon $clockOutTime = null,
        ?string $currentStatus = null
    ): self
    {
        return new self([
            'business_id'       => $client->business_id,
            'caregiver_id'      => $caregiver->id,
            'client_id'         => $client->id,
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
        return new self([
            'schedule_id'       => $schedule->id,
            'business_id'       => $schedule->business_id,
            'caregiver_id'      => $schedule->caregiver_id,
            'client_id'         => $schedule->client_id,
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
    }

    public static function getDefaultStatus(bool $hasBeenClockedOut, ?int $businessId = null): string
    {
        return $hasBeenClockedOut ? Shift::WAITING_FOR_CONFIRMATION : Shift::CLOCKED_IN;
    }

    public function withData(ShiftDataInterface ...$dataObjects): self
    {
        foreach($dataObjects as $object) {
            $this->attributes = array_merge($this->attributes, $object->toArray());
        }

        return $this;
    }

    public function create(ShiftDataInterface ...$dataObjects): Shift
    {
        return Shift::create($this->withData(...$dataObjects)->toArray());
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