<?php

namespace App\Events;

use App\BusinessChain;
use App\Events\Contracts\BusinessChainEvent;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class BusinessChainCreated implements BusinessChainEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var \App\BusinessChain
     */
    public $businessChain;

    public function __construct(BusinessChain $businessChain)
    {
        $this->businessChain = $businessChain;
    }

    public function getBusinessChain(): BusinessChain
    {
        return $this->businessChain;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }


}
