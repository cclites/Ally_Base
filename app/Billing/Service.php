<?php
namespace App\Billing;

use App\AuditableModel;
use App\Contracts\BelongsToChainsInterface;
use App\Traits\BelongsToOneChain;

/**
 * \App\Billing\Service
 *
 * @property int $id
 * @property int $chain_id
 * @property bool $default
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read \App\BusinessChain $businessChain
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\Service forAuthorizedChain(\App\User $authorizedUser = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\Service forChains($chains)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @mixin \Eloquent
 */
class Service extends AuditableModel implements BelongsToChainsInterface
{
    use BelongsToOneChain;

    protected $orderedColumn = 'name';

    protected $fillable = ['name', 'default', 'chain_id'];

    protected $casts = [
        'default' => 'bool',
        'chain_id' => 'int',
    ];

    /**
     * Get the default service for a business chain
     *
     * @param int $chainId
     * @return \App\Billing\Service|null
     */
    public static function getDefault(int $chainId): ?Service
    {
        return self::where('default', true)->where('chain_id', $chainId)->first();
    }
}