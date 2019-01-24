<?php
namespace App\Billing;

use App\Address;
use App\AuditableModel;
use App\Contracts\BelongsToChainsInterface;
use App\Contracts\ContactableInterface;
use App\PhoneNumber;
use App\Traits\BelongsToOneChain;
use Carbon\Carbon;

/**
 * App\Billing\Payer
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read \App\BusinessChain $businessChain
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Billing\PayerRate[] $rates
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\Payer forAuthorizedChain(\App\User $authorizedUser = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\Payer forChains($chains)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @mixin \Eloquent
 */
class Payer extends AuditableModel implements BelongsToChainsInterface, ContactableInterface
{
    use BelongsToOneChain;

    const PRIVATE_PAY_ID = 0;

    protected $orderedColumn = 'name';

    protected $guarded = ['id', 'rates'];

    protected $casts = [
        'chain_id' => 'int',
        'week_start' => 'int',
    ];

    protected $with = ['rates'];

    ////////////////////////////////////
    //// Relationship Methods
    ////////////////////////////////////

    function rates()
    {
        return $this->hasMany(PayerRate::class);
    }

    ////////////////////////////////////
    //// Instance Methods
    ////////////////////////////////////

    function isPrivatePay()
    {
        return $this->id === self::PRIVATE_PAY_ID;
    }

    /**
     * Get the default PayerRate for this payer
     *
     * @param string $date
     * @return \App\Billing\PayerRate|null
     */
    function getDefaultRate(string $date = 'now'): ?PayerRate
    {
        $date = Carbon::parse($date, 'UTC')->setTime(0, 0, 0);

        return $this->rates()
            ->whereNull('service_id')
            ->where('effective_start', '<=', $date)
            ->where('effective_end', '>=', $date)
            ->first();
    }

    /**
     * Remove existing PayerRates and update with the given
     * request values.
     *
     * @param array|null $rates
     * @return bool
     */
    public function syncRates(iterable $rates) : bool
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
                // remove all issues with ids that aren't in the current array
                PayerRate::where('payer_id', $this->id)
                    ->whereNotIn('id', $ids)
                    ->delete();

                // update the existing issues in case they changed
                foreach($existing as $item) {
                    if ($rate = PayerRate::where('id', $item['id'])->first()) {
                        $rate->update($item);
                    }
                }
            } else {
                // clear
                PayerRate::where('payer_id', $this->id)->delete();
            }

            // create new issues from the issues that have no id
            foreach($new as $item) {
                PayerRate::create(array_merge($item, ['payer_id' => $this->id]));
            }
            
            return true;
        } catch (\Exception $ex) {
            \Log::debug($ex->getMessage());
            return false;
        }
    }

    function name(): string
    {
        // TODO: Implement name() method.
    }

    function getAddress(): ?Address
    {
        return new Address([
            'address1' => $this->address1,
            'address2' => $this->address2,
            'city' => $this->city,
            'state' => $this->state,
            'zip' => $this->zip,
            'country' => 'US',
        ]);
    }

    function getPhoneNumber(): ?PhoneNumber
    {
        try {
            $phone = new PhoneNumber();
            $phone->input($this->phone_number);
            return $phone;
        }
        catch (\Exception $e) {}
        return null;
    }
}