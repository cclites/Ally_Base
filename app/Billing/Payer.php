<?php
namespace App\Billing;

use App\Address;
use App\AuditableModel;
use App\Billing\Contracts\ChargeableInterface;
use App\Billing\Exceptions\PayerAssignmentError;
use App\Billing\Payments\Methods\Offline;
use App\Business;
use App\Client;
use App\Contracts\BelongsToChainsInterface;
use App\Contracts\ContactableInterface;
use App\PhoneNumber;
use App\Traits\BelongsToOneChain;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * \App\Billing\Payer
 *
 * @property int $id
 * @property string $name
 * @property string|null $npi_number
 * @property int $week_start
 * @property string|null $address1
 * @property string|null $address2
 * @property string|null $city
 * @property string|null $state
 * @property string|null $zip
 * @property string|null $phone_number
 * @property string|null $fax_number
 * @property string|null $contact_name
 * @property int|null $chain_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $invoice_format
 * @property string|null $payment_method_type
 * @property int|null $payment_method_id
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read \App\BusinessChain|null $businessChain
 * @property-read \Illuminate\Database\Eloquent\Model|ChargeableInterface $paymentMethod
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Billing\PayerRate[] $rates
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\Payer forAuthorizedChain(\App\User $authorizedUser = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\Payer forChains($chains)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\Payer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\Payer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\Payer query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\Payer whereAddress1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\Payer whereAddress2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\Payer whereChainId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\Payer whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\Payer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\Payer whereFaxNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\Payer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\Payer whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\Payer whereNpiNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\Payer wherePaymentMethodId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\Payer wherePaymentMethodType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\Payer wherePhoneNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\Payer whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\Payer whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\Payer whereWeekStart($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\Payer whereZip($value)
 * @mixin \Eloquent
 */
class Payer extends AuditableModel implements BelongsToChainsInterface, ContactableInterface
{
    use BelongsToOneChain;

    const PRIVATE_PAY_ID = 0;
    const OFFLINE_PAY_ID = 1;

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

    function paymentMethod()
    {
        return $this->morphTo('payment_method');
    }

    ////////////////////////////////////
    //// Instance Methods
    ////////////////////////////////////

    function isPrivatePay()
    {
        return $this->id === self::PRIVATE_PAY_ID;
    }

    function isOffline()
    {
        return $this->id === self::OFFLINE_PAY_ID || (!$this->isPrivatePay() && !$this->payment_method_type);
    }

    function getUniqueKey(): string
    {
        if ($this->isPrivatePay()) {
            throw new PayerAssignmentError("The private payer record does not have a unique key.");
        }

        return (string) $this->id;
    }

    function setPaymentMethod(ChargeableInterface $paymentMethod): bool
    {
        if ($paymentMethod instanceof Model && $paymentMethod->getKey()) {
            return $this->paymentMethod()->associate($paymentMethod)->save();
        }

        if ($paymentMethod instanceof Business) {
            $this->payment_method_type = maps_from_model($paymentMethod);
        }

        if ($paymentMethod instanceof Offline) {
            $this->payment_method_type = null;
        }

        $this->payment_method_id = null;
        return $this->save();
    }

    function getPaymentMethod(): ?ChargeableInterface
    {
        if (maps_to_class($this->payment_method_type) === Business::class) {
            return new Business();
        }

        if ($this->isOffline()) {
            return new Offline();
        }

        return null;
    }

    /**
     * A shortcut to setting provider pay (no business ID since that will be resolved based on which business invoice is being paid)
     *
     * @return bool
     */
    function setProviderPay()
    {
        return $this->setPaymentMethod(new Business());
    }


    function name(): string
    {
        if ($this->isPrivatePay()) {
            // This method shouldn't really be used for private pay, use ClientPayer instead
            return 'Client';
        }

        return $this->name;
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

    /**
     * Get the ClaimService transmission method for the payer.
     *
     * @return ClaimService|null
     */
    public function getTransmissionMethod() : ?ClaimService
    {
        $method = $this->transmission_method;

        if (empty($method)) {
            return null;
        }

        return ClaimService::$method();
    }

    /**
     * Get payer code for claim transmissions.
     *
     * @return string|null
     */
    public function getPayerCode() : ?string
    {
        return $this->payer_code;
    }

    /**
     * Get plan code for claim transmissions.
     *
     * @return string|null
     */
    public function getPlanCode() : ?string
    {
        return $this->plan_code;
    }

    /**
     * Get the extra data that should be printed on invoices.
     *
     * @return array
     */
    function getExtraInvoiceData(): array
    {
        return [];
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
            'email' => $faker->email,
            'address1' => $faker->streetAddress,
            'npi_number' => $faker->randomNumber(9),
            'phone_number' => $faker->simple_phone,
            'fax_number' => $faker->simple_phone,
            'contact_name' => $faker->name,
        ];
    }
}