<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class AssignedTaskEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The assigned Task.
     *
     * @var \App\Task
     */
    public $task;

    /**
     * The URL of the Task.
     *
     * @var string
     */
    public $url;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($task)
    {
        $this->task = $task;
//        $this->url = route('business.tasks.show', ['task' => $task->id]);
        $this->url = route('business.tasks.index');
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $this->subject('You have been assigned a Task');

        return $this->view('emails.office-user.assigned-task');
    }
}
