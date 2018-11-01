<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ClientOnboarding extends Model
{
    protected $guarded = ['id'];

    public function activities()
    {
        return $this->belongsToMany(OnboardingActivity::class, 'client_onboarding_activities');
    }
}
