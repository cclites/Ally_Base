<?php
namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class CaregiverLicense extends Model
{
    protected $table = 'caregiver_licenses';

    ///////////////////////////////////////////
    /// Relationship Methods
    ///////////////////////////////////////////

    public function caregiver()
    {
        return $this->belongsTo(Caregiver::class);
    }

    ///////////////////////////////////////////
    /// Other Methods
    ///////////////////////////////////////////

    public function isExpired()
    {
        $expireDate = new Carbon($this->expires_at);
        return ($expireDate < Carbon::now());
    }
}