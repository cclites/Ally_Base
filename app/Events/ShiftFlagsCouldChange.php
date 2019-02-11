<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ShiftFlagsCouldChange
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
    public function __construct(\App\Shift $shift)
    {
        $this->shift = $shift;
    }
}
