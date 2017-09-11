<?php

namespace App;

use App\Traits\IsUserRole;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use IsUserRole;

    protected $table = 'clients';
    public $timestamps = false;
    public $fillable = [];

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function upcomingPayments()
    {
        return $this->hasMany(PaymentQueue::class);
    }
}
