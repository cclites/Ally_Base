<?php
namespace App;

/**
 * App\ClientExcludedCaregiver
 *
 * @property int $id
 * @property int $client_id
 * @property int $caregiver_id
 * @property string|null $note
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read \App\Caregiver $caregiver
 * @property-read \App\Client $client
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientExcludedCaregiver whereCaregiverId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientExcludedCaregiver whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientExcludedCaregiver whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientExcludedCaregiver whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientExcludedCaregiver whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientExcludedCaregiver whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ClientExcludedCaregiver extends AuditableModel
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
