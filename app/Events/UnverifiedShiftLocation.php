<?php

namespace App\Events;

use App\Shift;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class UnverifiedShiftLocation
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    /**
     * @var \App\Shift
     */
    public $shift;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Shift $shift)
    {
        $this->shift = $shift;
    }

}
