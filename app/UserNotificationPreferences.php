<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\UserNotificationPreferences
 *
 * @property int $id
 * @property int $user_id
 * @property string $key
 * @property int $sms
 * @property int $email
 * @property int $system
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UserNotificationPreferences newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UserNotificationPreferences newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UserNotificationPreferences query()
 * @mixin \Eloquent
 */
class UserNotificationPreferences extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];
    
    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    public $with = [];
    
    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [];
    
    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        //
        parent::boot();
    }
    
    // **********************************************************
    // RELATIONSHIPS
    // **********************************************************

    /**
     * Get the related user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
    */
    public function user()
    {
        return $this->hasOne(User::class);
    }

    // **********************************************************
    // MUTATORS
    // **********************************************************
    
    // **********************************************************
    // QUERY SCOPES
    // **********************************************************
    
    // **********************************************************
    // OTHER FUNCTIONS
    // **********************************************************
}
