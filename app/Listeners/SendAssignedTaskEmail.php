<?php

namespace App\Listeners;

use App\Events\TaskAssigned;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Mail\AssignedTaskEmail;

class SendAssignedTaskEmail implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Handle the event.
     *
     * @param TaskAssigned  $event
     * @return void
     */
    public function handle(TaskAssigned $event)
    {
        \Mail::to($event->task->assignedUser->email)
            ->send(new AssignedTaskEmail($event->task));
    }
}
