<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * App\ShiftConfirmation
 *
 * @property int $id
 * @property int $client_id
 * @property string $token
 * @property \Carbon\Carbon|null $confirmed_at
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\Client $client
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Shift[] $shifts
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShiftConfirmation whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShiftConfirmation whereConfirmedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShiftConfirmation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShiftConfirmation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShiftConfirmation whereToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShiftConfirmation whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property-read int|null $shifts_count
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShiftConfirmation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShiftConfirmation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShiftConfirmation query()
 */
class ShiftConfirmation extends Model
{
    /**
     * The attributes that should not be mass-assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['confirmed_at'];

    /**
     * Get the shifts relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
    */
    public function shifts()
    {
        return $this->belongsToMany(Shift::class, 'shift_confirmation_shifts');
    }

    /**
     * Get the client relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
    */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get ShiftConfirmation by token.
     *
     * @param string $token
     * @return \App\ShiftConfirmation|null
     */
    public static function findToken($token)
    {
        return self::with('shifts', 'client')
            ->where('token', $token)
            ->first();
    }

    // **********************************************************
    // ScrubsForSeeding Methods
    // **********************************************************
    use \App\Traits\ScrubsForSeeding;
    
    /**
     * Get an array of scrubbed data to replace the original.
     *
     * @param \Faker\Generator $faker
     * @param bool $fast
     * @param null|\Illuminate\Database\Eloquent\Model $item
     * @return array
     */
    public static function getScrubbedData(\Faker\Generator $faker, bool $fast, ?\Illuminate\Database\Eloquent\Model $item) : array
    {
        return [
            'token' => Str::random(64),
        ];
    }
}
