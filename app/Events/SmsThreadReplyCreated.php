<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use App\SmsThreadReply;

class SmsThreadReplyCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var \App\Reply
     */
    public $reply;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(SmsThreadReply $reply)
    {
        $this->reply = $reply;
    }
}
