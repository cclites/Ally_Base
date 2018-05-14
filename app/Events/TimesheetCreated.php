<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use App\Timesheet;

class TimesheetCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     *
     * @var \App\Timesheet
     */
    public $timesheet;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Timesheet $timesheet)
    {
        $this->timesheet = $timesheet;
    }
}
