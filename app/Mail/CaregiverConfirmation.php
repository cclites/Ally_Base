<?php

namespace App\Mail;

use App\BusinessChain;
use App\Caregiver;
use App\Confirmations\Confirmation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CaregiverConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public $caregiver;
    public $businessChain;
    public $url;
    public $token;

    /**
     * Create a new message instance.
     *
     * @param \App\Caregiver $caregiver
     * @param \App\BusinessChain $businessChain
     */
    public function __construct(Caregiver $caregiver, BusinessChain $businessChain)
    {
        $this->caregiver = $caregiver;
        $this->businessChain = $businessChain;
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
        $this->subject('Please confirm your information for ' . $this->businessChain->name);
        return $this->view('emails.caregiver-confirmation');
    }
}
