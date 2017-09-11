<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Business extends Model
{
    protected $table = 'businesses';

    public function clients()
    {
        return $this->belongsToMany(Client::class, 'business_clients')
            ->withTimestamps()
            ->withPivot([
                'business_fee',
                'default_payment_type',
                'default_payment_id',
                'backup_payment_type',
                'backup_payment_id',
                'created_at',
                'updated_at'
            ]);
    }

    public function caregivers()
    {
        return $this->belongsToMany(Caregiver::class, 'business_caregivers')
            ->withPivot([
                'type'
            ]);
    }

    public function deposits()
    {
        return $this->hasMany(Deposit::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function upcomingPayments()
    {
        return $this->hasMany(PaymentQueue::class);
    }

    public function users()
    {
        return $this->belongsToMany(OfficeUser::class, 'business_office_users');
    }
}
