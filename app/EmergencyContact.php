<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Class EmergencyContact
 *
 * @package App
 * @property string $name
 * @property string $phone_number
 * @property string $relationship
 * @property User $user
 * @property int $id
 * @property int $user_id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\EmergencyContact whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\EmergencyContact whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\EmergencyContact whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\EmergencyContact wherePhoneNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\EmergencyContact whereRelationship($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\EmergencyContact whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\EmergencyContact whereUserId($value)
 * @mixin \Eloquent
 */
class EmergencyContact extends Model
{
    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
