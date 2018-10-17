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

    public $client;
    public $shifts;
    public $total;
    public $business;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($client, $shifts, $total, $business)
    {
        $this->client = $client;
        $this->shifts = $shifts;
        $this->total = $total;
        $this->business = $business;
        $this->confirmUrl = config('app.url') . '/confirm-shifts';
        $this->modifyUrl = config('app.url') . '/modify-shifts';
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('')
            ->markdown('emails.client.shift-summary');
    }
}
