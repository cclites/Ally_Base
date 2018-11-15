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
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered()
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
    protected $guarded = ['id'];

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

}
