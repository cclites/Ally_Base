<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\OnboardStatusHistory
 *
 * @property int $id
 * @property int $client_id
 * @property string $status
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OnboardStatusHistory whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OnboardStatusHistory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OnboardStatusHistory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OnboardStatusHistory whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OnboardStatusHistory whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class OnboardStatusHistory extends Model
{
    protected $table = 'onboard_status_history';
    protected $guarded = ['id'];

    ///////////////////////////////////////////
    /// Relationship Methods
    ///////////////////////////////////////////



    ////////////////////////////////////////////
    /// Other Methods
    ///////////////////////////////////////////

}
