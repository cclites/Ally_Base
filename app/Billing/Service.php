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
 * @property string $name
 * @property string|null $code
 * @property bool $default
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read \App\BusinessChain $businessChain
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\Service forAuthorizedChain(\App\User $authorizedUser = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\Service forChains($chains)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @mixin \Eloquent
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read int|null $audits_count
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\Service newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\Service newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\Service query()
 */
class Service extends AuditableModel implements BelongsToChainsInterface
{
    use BelongsToOneChain;

    /**
     * The name of the initial default service for new registries
     */
    const DEFAULT_SERVICE_NAME = "General";

    protected $orderedColumn = 'name';

    protected $fillable = ['name', 'code', 'default', 'chain_id'];

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

    /**
     * Set the default service for a business chain
     *
     * @param int $chainId
     * @param \App\Billing\Service $service
     * @return bool
     * @throws \Exception
     */
    public static function setDefault(int $chainId, Service $service): bool
    {
        if ($service->chain_id !== $chainId) throw new \Exception('Unable to set a default, chain id mismatch.');

        if ($service->update(['default' => true])) {
            return self::where('chain_id', $chainId)
                ->where('id', '!=', $service->id)
                ->update(['default' => false]);
        }

        return false;
    }
}