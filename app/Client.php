<?php

namespace App;

use App\Traits\IsUserRole;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use IsUserRole;

    protected $table = 'clients';
    public $timestamps = false;
    public $fillable = [
        'business_id',
        'business_fee',
        'default_payment_type',
        'default_payment_id',
        'backup_payment_type',
        'backup_payment_id',
    ];

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function upcomingPayments()
    {
        return $this->hasMany(PaymentQueue::class);
    }

    public function evvAddress()
    {
        return $this->hasOne(Address::class, 'user_id', 'id')
            ->where('type', 'evv');
    }

    public function evvPhone()
    {
        return $this->hasOne(PhoneNumber::class, 'user_id', 'id')
            ->where('type', 'evv');
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }
}
