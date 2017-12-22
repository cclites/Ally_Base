<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Deposit extends Model
{
    protected $table = 'deposits';
    protected $guarded = ['id'];
    protected $appends = ['week'];

    ///////////////////////////////////////////
    /// Relationship Methods
    ///////////////////////////////////////////

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

    public function shifts()
    {
        return $this->belongsToMany(Shift::class, 'deposit_shifts');
    }

    ///////////////////////////////////////////
    /// Mutators
    ///////////////////////////////////////////

    public function getWeekAttribute()
    {
        if (!$this->created_at) {
            return null;
        }

        $date = $this->created_at->copy()->subWeek();
        return [
            'start' => $date->setIsoDate($date->year, $date->weekOfYear)->toDateString(),
            'end' => $date->setIsoDate($date->year, $date->weekOfYear, 7)->toDateString()
        ];
    }
}
