<?php

namespace App\Events;

use App\Shift;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class ShiftCreated
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
