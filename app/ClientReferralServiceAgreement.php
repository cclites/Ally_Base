<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ClientReferralServiceAgreement extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['payment_options' => 'array'];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

}
