<?php

namespace App\Mail;

use App\Confirmations\Confirmation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ClientConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public $client;
    public $business;
    public $url;
    public $token;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($client, $business)
    {
        $this->client = $client;
        $this->business = $business;
        $confirmation = new Confirmation($client);
        $this->token = $confirmation->getToken();
        $this->url = route('confirm.client', [$this->token]);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $this->subject('Please confirm your information for ' . $this->business->name);
        return $this->view('emails.client-confirmation');
    }
}
