<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

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
