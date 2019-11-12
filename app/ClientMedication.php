<?php

namespace App;

use Illuminate\Support\Facades\Crypt;

/**
 * App\ClientMedication
 *
 * @property int $id
 * @property int $client_id
 * @property mixed $type
 * @property mixed $dose
 * @property mixed $frequency
 * @property mixed $route
 * @property mixed $new_changed
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientMedication whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientMedication whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientMedication whereDose($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientMedication whereFrequency($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientMedication whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientMedication whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientMedication whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ClientMedication extends BaseModel
{
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['was_changed' => 'boolean'];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    // **********************************************************
    // RELATIONSHIPS
    // **********************************************************

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    // **********************************************************
    // MUTATORS
    // **********************************************************

    public function setTypeAttribute($value)
    {
        $this->attributes['type'] = Crypt::encrypt($value);
    }

    public function getTypeAttribute()
    {
        return empty($this->attributes['type']) ? null : Crypt::decrypt($this->attributes['type']);
    }

    public function setDoseAttribute($value)
    {
        $this->attributes['dose'] = Crypt::encrypt($value);
    }

    public function getDoseAttribute()
    {
        return empty($this->attributes['dose']) ? null : Crypt::decrypt($this->attributes['dose']);
    }

    public function setFrequencyAttribute($value)
    {
        $this->attributes['frequency'] = Crypt::encrypt($value);
    }

    public function getFrequencyAttribute()
    {
        return empty($this->attributes['frequency']) ? null : Crypt::decrypt($this->attributes['frequency']);
    }

    public function setDescriptionAttribute($value)
    {
        $this->attributes['description'] = Crypt::encrypt($value);
    }

    public function getDescriptionAttribute()
    {
        return empty($this->attributes['description']) ? null : Crypt::decrypt($this->attributes['description']);
    }

    public function setSideEffectsAttribute($value)
    {
        $this->attributes['side_effects'] = Crypt::encrypt($value);
    }

    public function getSideEffectsAttribute()
    {
        return empty($this->attributes['side_effects']) ? null : Crypt::decrypt($this->attributes['side_effects']);
    }

    public function setNotesAttribute($value)
    {
        $this->attributes['notes'] = Crypt::encrypt($value);
    }

    public function getNotesAttribute()
    {
        return empty($this->attributes['notes']) ? null : Crypt::decrypt($this->attributes['notes']);
    }

    public function setTrackingAttribute($value)
    {
        $this->attributes['tracking'] = Crypt::encrypt($value);
    }

    public function getTrackingAttribute()
    {
        return empty($this->attributes['tracking']) ? null : Crypt::decrypt($this->attributes['tracking']);
    }

    public function setRouteAttribute($value)
    {
        $this->attributes['route'] = Crypt::encrypt($value);
    }

    public function getRouteAttribute()
    {
        return empty($this->attributes['route']) ? null : Crypt::decrypt($this->attributes['route']);
    }

    public function getNewChangedAttribute()
    {
        return $this->was_changed ? '(C)' : '(N)';
    }
}
