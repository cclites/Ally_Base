<?php
namespace App\Billing;

use App\AuditableModel;
use App\Contracts\BelongsToChainsInterface;
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
class Payer extends AuditableModel implements BelongsToChainsInterface
{
    use BelongsToOneChain;

    protected $orderedColumn = 'name';

    protected $fillable = ['name', 'npi_number', 'chain_id'];

    protected $casts = [
        'chain_id' => 'int',
    ];

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
}