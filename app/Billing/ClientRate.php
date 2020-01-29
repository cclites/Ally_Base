<?php
namespace App\Billing;

use App\AuditableModel;
use App\Caregiver;
use App\Client;

/**
 * App\Billing\ClientRate
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read \App\Caregiver $caregiver
 * @property-read \App\Client $client
 * @property-read \App\Billing\Payer $payer
 * @property-read \App\Billing\Service $service
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @mixin \Eloquent
 * @property int $id
 * @property int $client_id
 * @property int|null $payer_id
 * @property int|null $service_id
 * @property int|null $caregiver_id
 * @property string $effective_start
 * @property string $effective_end
 * @property float $caregiver_hourly_rate
 * @property float $caregiver_fixed_rate
 * @property float $client_hourly_rate
 * @property float $client_fixed_rate
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read int|null $audits_count
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\ClientRate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\ClientRate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\ClientRate query()
 */
class ClientRate extends AuditableModel
{
    protected $guarded = ['id'];

    protected $casts = [
        'client_id' => 'int',
        'payer_id' => 'int',
        'service_id' => 'int',
        'caregiver_id' => 'int',
        'caregiver_hourly_rate' => 'float',
        'caregiver_fixed_rate' => 'float',
        'client_hourly_rate' => 'float',
        'client_fixed_rate' => 'float',
    ];

    ////////////////////////////////////
    //// Relationship Methods
    ////////////////////////////////////

    function client()
    {
        return $this->belongsTo(Client::class);
    }

    function payer()
    {
        return $this->belongsTo(Payer::class);
    }

    function service()
    {
        return $this->belongsTo(Service::class);
    }

    function caregiver()
    {
        return $this->belongsTo(Caregiver::class);
    }

    ////////////////////////////////////
    //// Instance Methods
    ////////////////////////////////////

    /**
     * Remove missing ClientRates and update existing with the given
     * request values.
     *
     * @param \App\Client $client
     * @param array|null $rates
     * @return bool
     */
    public static function sync(Client $client, ?iterable $rates) : bool
    {
        try {
            $new = collect($rates)->filter(function($item) {
                return ! isset($item['id']);
            });

            $existing = collect($rates)->filter(function($item) {
                return isset($item['id']);
            });

            $ids = $existing->pluck('id');
            if (count($ids)) {
                // remove all items with ids that aren't in the current array
                ClientRate::where('client_id', $client->id)
                    ->whereNotIn('id', $ids)
                    ->delete();

                // update the existing items in case they changed
                foreach($existing as $item) {
                    if ($rate = ClientRate::where('id', $item['id'])->first()) {
                        $rate->update($item);
                    }
                }
            } else {
                // clear
                ClientRate::where('client_id', $client->id)->delete();
            }

            // create new issues from the issues that have no id
            foreach($new as $item) {
                ClientRate::create(array_merge($item, ['client_id' => $client->id]));
            }

            return true;
        } catch (\Exception $ex) {
            \Log::debug($ex->getMessage());
            return false;
        }
    }

    /**
     * Add a new rate, attaching the caregiver record
     *
     * @param \App\Client $client
     * @param array $data
     * @return mixed
     */
    public static function add(Client $client, array $data)
    {
        $data['client_id'] = $client->id;

        if (isset($data['caregiver_id']) && !$client->hasCaregiver($data['caregiver_id'])) {
            $client->caregivers()->attach($data['caregiver_id']);
        }

        return self::create($data);
    }
}