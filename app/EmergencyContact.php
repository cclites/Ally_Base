<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Class EmergencyContact
 * @package App
 * @property string $name
 * @property string $phone_number
 * @property string $relationship
 * @property User $user
 */
class EmergencyContact extends Model
{
    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
