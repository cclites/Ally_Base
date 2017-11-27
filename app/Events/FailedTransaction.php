<?php

namespace App\Events;

use App\GatewayTransaction;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class FailedTransaction
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var \App\GatewayTransaction
     */
    public $transaction;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(GatewayTransaction $transaction)
    {
        $this->transaction = $transaction;
    }
}
