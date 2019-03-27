<?php

namespace App\Mail;

use App\Businesses\Timezone;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ClientShiftSummaryEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * The client the email will be sent to.
     *
     * @var App\Client
     */
    public $client;

    /**
     * The shift summary report data.
     *
     * @var array
     */
    public $shifts;

    /**
     * The total cost to the client for all shifts.
     *
     * @var string
     */
    public $total;

    /**
     * The business name of the client.
     *
     * @var App\Business
     */
    public $businessName;

    /**
     * The shift confirmation token.
     *
     * @var string
     */
    public $token;

    /**
     * The confirmation token used to confirm the shifts.
     *
     * @var string
     */
    public $confirmToken;

    /**
     * @var string
     */
    public $timezone;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($client, $shifts, $total, $businessName, $token)
    {
        $this->client = $client;
        $this->shifts = $shifts;
        $this->total = $total;
        $this->businessName = $businessName;
        $this->token = $token;
        $this->confirmToken = $token;
        $this->timezone = Timezone::getTimezone($client->business_id) ?: 'America/New_York';
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject("Please Confirm Your Visit Details For {$this->businessName}")
            ->markdown('emails.client.shift-summary');
    }
}
