<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\ClientExcludedCaregiver
 *
 * @property int $id
 * @property int $client_id
 * @property int $caregiver_id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\Caregiver $caregiver
 * @property-read \App\Client $client
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientExcludedCaregiver whereCaregiverId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientExcludedCaregiver whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientExcludedCaregiver whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientExcludedCaregiver whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientExcludedCaregiver whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ClientExcludedCaregiver extends Model
{
    protected $guarded = ['id'];

    protected $with = ['caregiver'];

    public function caregiver()
    {
        return $this->belongsTo(Caregiver::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
