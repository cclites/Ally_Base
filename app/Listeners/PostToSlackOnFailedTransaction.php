<?php

namespace App\Listeners;

use App\Contracts\ChatServiceInterface;
use App\Events\FailedTransaction;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class PostToSlackOnFailedTransaction
{
    public $chatService;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(ChatServiceInterface $chatService)
    {
        $this->chatService = $chatService;
    }

    /**
     * Handle the event.
     *
     * @param  FailedTransaction  $event
     * @return void
     */
    public function handle(FailedTransaction $event)
    {
        if (!$lastHistory = $event->transaction->lastHistory) {
            return;
        }

        $template = "A failed transaction was found.\n
        Transaction ID:%s
        Transaction Type: %s
        Transaction Amount: %s
        Last Action: %s
        Action Date: %s
        Link: %s";

        $message = sprintf(
            $template,
            $event->transaction->id,
            $event->transaction->transaction_type,
            $lastHistory->amount,
            $lastHistory->action,
            $lastHistory->created_at->setTimezone('America/New_York')->format('m/d/Y H:i:s T'),
            route('admin.transactions.show', [$event->transaction->id])
        );

        if ($this->chatService->isAvailable()) {
            $this->chatService->post($message);
        }
    }
}
