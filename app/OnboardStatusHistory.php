<?php
namespace App;

/**
 * App\OnboardStatusHistory
 *
 * @property int $id
 * @property int $client_id
 * @property string $status
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OnboardStatusHistory whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OnboardStatusHistory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OnboardStatusHistory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OnboardStatusHistory whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OnboardStatusHistory whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class OnboardStatusHistory extends BaseModel
{
    protected $table = 'onboard_status_history';
    protected $guarded = ['id'];

}
