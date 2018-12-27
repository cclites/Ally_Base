<?php
namespace App;

use App\Contracts\BelongsToBusinessesInterface;
use App\Traits\BelongsToOneBusiness;

/**
 * App\ReferralSource
 *
 * @property int $id
 * @property int $business_id
 * @property string $organization
 * @property string $contact_name
 * @property string $phone
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read \App\Business $business
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Client[] $client
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Note[] $notes
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Prospect[] $prospect
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ReferralSource forBusinesses($businessIds)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ReferralSource forRequestedBusinesses($businessIds = null, \App\User $authorizedUser = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ReferralSource whereBusinessId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ReferralSource whereContactName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ReferralSource whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ReferralSource whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ReferralSource whereOrganization($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ReferralSource wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ReferralSource whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ReferralSource extends AuditableModel implements BelongsToBusinessesInterface
{
    use BelongsToOneBusiness;

    protected $orderedColumn = 'organization';

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

    public function notes()
    {
        return $this->hasMany(Note::class);
    }
}
