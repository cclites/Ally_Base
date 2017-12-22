<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $table = 'payments';
    protected $guarded = ['id'];
    protected $appends = ['week'];

    ///////////////////////////////////////////
    /// Relationship Methods
    ///////////////////////////////////////////

    public function shifts()
    {
        return $this->hasMany(Shift::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function caregiver()
    {
        return $this->belongsTo(Caregiver::class);
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function transaction()
    {
        return $this->belongsTo(GatewayTransaction::class, 'transaction_id');
    }

    ////////////////////////////////////////////
    /// Other Methods
    ///////////////////////////////////////////

    public function getWeekAttribute()
    {
        if ($this->shifts()->exists()) {
            $checked_in_time = $this->shifts->first()->checked_in_time;
            return (object) [
                'start' => $checked_in_time->setIsoDate($checked_in_time->year, $checked_in_time->weekOfYear)->toDateString(),
                'end' => $checked_in_time->setIsoDate($checked_in_time->year, $checked_in_time->weekOfYear, 7)->toDateString()
            ];
        }
        return null;
    }
}
