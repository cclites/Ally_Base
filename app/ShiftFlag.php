<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\ShiftFlag
 *
 * @property int $id
 * @property int $shift_id
 * @property string $flag
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShiftFlag whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShiftFlag whereFlag($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShiftFlag whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShiftFlag whereShiftId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShiftFlag whereUpdatedAt($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShiftFlag newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShiftFlag newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShiftFlag query()
 */
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
        self::DURATION_MISMATCH,
        self::MISSING_RATES,
        self::MODIFIED,
        self::OUTSIDE_AUTH,
        self::TIME_EXCESSIVE,
    ];

    const ADDED = 'added';
    const CONVERTED = 'converted';
    const DUPLICATE = 'duplicate';
    const DURATION_MISMATCH = 'duration_mismatch';
    const MISSING_RATES = 'missing_rates';
    const MODIFIED = 'modified';
    const OUTSIDE_AUTH = 'outside_auth';
    const TIME_EXCESSIVE = 'time_excessive';
}
