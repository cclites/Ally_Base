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
}
