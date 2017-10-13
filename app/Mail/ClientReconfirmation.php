<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ClientReconfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public $client;
    public $business;
    public $url;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($client, $business)
    {
        $this->client = $client;
        $this->business = $business;
        $this->url = route('reconfirm.encrypted_id', [$client->getEncryptedKey()]);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $this->subject('Please confirm your information for ' . $this->business->name);
        return $this->view('emails.reconfirm');
    }
}
