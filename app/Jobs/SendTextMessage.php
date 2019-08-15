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
     * @var bool
     */
    public $debugMode = false;

    /**
     * Create a new job instance.
     *
     * @param string $to
     * @param string $message
     * @param string|null $from
     * @param bool $debugMode
     */
    public function __construct(string $to, string $message, string $from = null, bool $debugMode = false)
    {
        $this->to = $to;
        $this->message = $message;
        $this->from = $from;
        $this->debugMode = $debugMode;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws \Twilio\Exceptions\ConfigurationException
     */
    public function handle()
    {
        $service = new PhoneService(null, $this->debugMode);
        
        if (!empty($this->from)) {
            $service->setFromNumber($this->from);
        }

        $service->sendTextMessage($this->to, $this->message);
    }
}
