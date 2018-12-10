<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ShiftFlag extends Model
{
    /**
     * The attributes that are not mass-assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    ////////////////////////////////////
    //// Shift Flags
    ////////////////////////////////////

    const FLAGS = [
        self::ADDED,
        self::CONVERTED,
        self::DUPLICATE,
        self::MODIFIED,
        self::OUTSIDE_AUTH,
        self::TIME_EXCESSIVE,
    ];

    const ADDED = 'added';
    const CONVERTED = 'converted';
    const DUPLICATE = 'duplicate';
    const MODIFIED = 'modified';
    const OUTSIDE_AUTH = 'outside_auth';
    const TIME_EXCESSIVE = 'time_excessive';
}
