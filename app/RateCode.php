<?php
namespace App;

use App\Contracts\BelongsToBusinessesInterface;
use App\Traits\BelongsToOneBusiness;

/**
 * App\RateCode
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @method static \Illuminate\Database\Eloquent\Builder|\App\RateCode forAuthorizedBusinesses($businessIds, \App\User $authorizedUser = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\RateCode forBusinesses($businessIds)
 * @mixin \Eloquent
 */
class RateCode extends AuditableModel implements BelongsToBusinessesInterface
{
    use BelongsToOneBusiness;

    protected $table = 'rate_codes';

    protected $fillable = [
        'name', 'type', 'rate', 'fixed'
    ];
}
