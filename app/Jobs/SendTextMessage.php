<?php

namespace App\Jobs;

use App\Services\PhoneService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendTextMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var string
     */
    public $to;

    /**
     * @var string
     */
    public $message;

    /**
     * @var string
     */
    public $from;
    
    /**
     * Create a new job instance.
     *
     * @param string $to
     * @param string $message
     */
    public function __construct(string $to, string $message, string $from = null)
    {
        $this->to = $to;
        $this->message = $message;
        $this->from = $from;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $service = new PhoneService;
        
        if (!empty($this->from)) {
            $service->setFromNumber($this->from);
        }

        $service->sendTextMessage($this->to, $this->message);
    }
}
