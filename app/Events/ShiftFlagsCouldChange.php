<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

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
     * @param \App\Shift $shift
     */
    public function __construct(\App\Shift $shift)
    {
        $this->shift = $shift;
    }
}
