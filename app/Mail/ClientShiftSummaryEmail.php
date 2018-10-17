<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ClientShiftSummaryEmail extends Mailable
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
     * The business of the client.
     *
     * @var App\Business
     */
    public $business;

    /**
     * The shift confirmation token.
     *
     * @var string
     */
    public $token;

    /**
     * The URL to confirm the shifts.
     *
     * @var string
     */
    public $confirmUrl;

    /**
     * The URL to modify the shifts.
     *
     * @var string
     */
    public $modifyUrl;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($client, $shifts, $total, $business, $token)
    {
        $this->client = $client;
        $this->shifts = $shifts;
        $this->total = $total;
        $this->business = $business;
        $this->token = $token;
        $this->confirmUrl = config('app.url') . "/confirm-shifts/$token";
        $this->modifyUrl = config('app.url') . '/modify-shifts';
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Service Week Visit Summary')
            ->markdown('emails.client.shift-summary');
    }
}
