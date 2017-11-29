<?php

namespace App\Mail;

use App\Confirmations\Confirmation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CaregiverConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public $caregiver;
    public $business;
    public $url;
    public $token;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($caregiver, $business)
    {
        $this->caregiver = $caregiver;
        $this->business = $business;
        $confirmation = new Confirmation($caregiver);
        $this->token = $confirmation->getToken();
        $this->url = route('confirm.caregiver', [$this->token]);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $this->subject('Please confirm your information for ' . $this->business->name);
        return $this->view('emails.caregiver-confirmation');
    }
}
