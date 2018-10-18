<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

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
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
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
}
