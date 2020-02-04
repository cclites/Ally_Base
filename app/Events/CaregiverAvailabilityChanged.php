<?php

namespace App\Events;

use App\Caregiver;
use App\CaregiverAvailability;
use App\CaregiverDayOff;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CaregiverAvailabilityChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $caregiver;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Caregiver $caregiver)
    {
        $this->caregiver = $caregiver;
    }

    public function getCaregiver(): Caregiver
    {
        return $this->caregiver;
    }

}
