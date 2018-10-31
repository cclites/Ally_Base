<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class ReferralSource extends Model
{
    protected $fillable = [
        'business_id',
        'organization',
        'contact_name',
        'phone'
    ];

    public function business() {
        return $this->belongsTo(Business::class);
    }

    public function client() {
        return $this->hasMany(Client::class);
    }

    public function prospect() {
        return $this->hasMany(Prospect::class);
    }
}
