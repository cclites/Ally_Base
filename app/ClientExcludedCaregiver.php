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
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
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

    const REASON_SERVICE_NOT_NEEDED = 'service_not_needed';
    const REASON_QUIT = 'quit';
    const REASON_UNHAPPY_CLIENT = 'unhappy_client';
    const REASON_NO_SHOWS = 'no_shows';
    const REASON_RETIRED = 'retired';
    const REASON_OTHER = 'other';
    
    public function caregiver()
    {
        return $this->belongsTo(Caregiver::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get a list of all the valid exclusion reasons.
     *
     * @return array
     */
    public static function exclusionReasons() : array
    {
        return [
            self::REASON_SERVICE_NOT_NEEDED,
            self::REASON_QUIT,
            self::REASON_UNHAPPY_CLIENT,
            self::REASON_NO_SHOWS,
            self::REASON_RETIRED,
            self::REASON_OTHER,
        ];
    }
}
