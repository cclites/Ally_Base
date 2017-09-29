<?php


namespace App;

use Crypt;
use Illuminate\Database\Eloquent\Model;

class CreditCard extends Model
{
    protected $table = 'credit_cards';
    protected $guarded = ['id'];
    protected $hidden = ['number'];
    protected $appends = ['last_four'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getLastFourAttribute()
    {
        return substr($this->number, -4);
    }

    public function setNumberAttribute($value)
    {
        $this->attributes['number'] = Crypt::encrypt($value);
    }

    public function getNumberAttribute()
    {
        return empty($this->attributes['number']) ? null : Crypt::decrypt($this->attributes['number']);
    }
}
