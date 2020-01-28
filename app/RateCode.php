<?php
namespace App;

use App\Contracts\BelongsToBusinessesInterface;
use App\Traits\BelongsToOneBusiness;

/**
 * App\RateCode
 *
 * @property int $id
 * @property int $business_id
 * @property string $name
 * @property string $type
 * @property float|null $rate
 * @property int $fixed
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @method static \Illuminate\Database\Eloquent\Builder|\App\RateCode forBusinesses($businessIds)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\RateCode forRequestedBusinesses($businessIds = null, \App\User $authorizedUser = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\RateCode whereBusinessId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\RateCode whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\RateCode whereFixed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\RateCode whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\RateCode whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\RateCode whereRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\RateCode whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\RateCode whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property-read int|null $audits_count
 * @method static \Illuminate\Database\Eloquent\Builder|\App\RateCode newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\RateCode newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\RateCode query()
 */
class RateCode extends AuditableModel implements BelongsToBusinessesInterface
{
    use BelongsToOneBusiness;

    protected $table = 'rate_codes';
    protected $orderedColumn = 'name';

    protected $fillable = [
        'name', 'type', 'rate', 'fixed'
    ];
}
