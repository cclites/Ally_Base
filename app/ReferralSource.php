<?php
namespace App;

use App\Contracts\BelongsToBusinessesInterface;
use App\Traits\BelongsToOneBusiness;

/**
 * App\ReferralSource
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read \App\Business $business
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Client[] $client
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Prospect[] $prospect
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ReferralSource forAuthorizedBusinesses($businessIds, \App\User $authorizedUser = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ReferralSource forBusinesses($businessIds)
 * @mixin \Eloquent
 */
class ReferralSource extends AuditableModel implements BelongsToBusinessesInterface
{
    use BelongsToOneBusiness;

    protected $fillable = [
        'business_id',
        'organization',
        'contact_name',
        'phone'
    ];

    public function business() {
        return $this->belongsTo(Business::class);
    }

    public function client() {
        return $this->hasMany(Client::class);
    }

    public function prospect() {
        return $this->hasMany(Prospect::class);
    }
}
