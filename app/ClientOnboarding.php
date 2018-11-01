<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ClientOnboarding extends Model
{
    protected $guarded = ['id'];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function activities()
    {
        return $this->belongsToMany(OnboardingActivity::class, 'client_onboarding_activities')
            ->withPivot('assistance_level');
    }

    public function signature()
    {
        return $this->morphOne(Signature::class, 'signable');
    }
}
