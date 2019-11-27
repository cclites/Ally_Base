<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AdminPin extends Model
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

    // **********************************************************
    // MUTATORS
    // **********************************************************

    // **********************************************************
    // QUERY SCOPES
    // **********************************************************

    // **********************************************************
    // OTHER FUNCTIONS
    // **********************************************************

    /**
     * Verify access using specified pin.
     *
     * @param string $pin
     * @param string $access
     * @return mixed
     */
    public static function verify(string $pin, string $access)
    {
        return AdminPin::where('pin', '=', $pin)
            ->where('access', '=', $access)
            ->exists();
    }
}
