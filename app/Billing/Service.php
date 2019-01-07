<?php
namespace App\Billing;

use App\AuditableModel;
use App\Contracts\BelongsToChainsInterface;
use App\Traits\BelongsToOneChain;

class Service extends AuditableModel implements BelongsToChainsInterface
{
    use BelongsToOneChain;

    protected $fillable = ['name', 'default', 'chain_id'];
    protected $casts = [
        'default' => 'boolean'
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