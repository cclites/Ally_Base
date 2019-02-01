<?php
namespace App;

use App\Contracts\BelongsToChainsInterface;
use App\Traits\BelongsToOneChain;

/**
 * App\ReferralSource
 *
 * @property int $id
 * @property string $organization
 * @property string $contact_name
 * @property string $phone
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Client[] $client
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Note[] $notes
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Prospect[] $prospect
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ReferralSource whereContactName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ReferralSource whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ReferralSource whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ReferralSource whereOrganization($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ReferralSource wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ReferralSource whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ReferralSource extends AuditableModel implements BelongsToChainsInterface
{
    use BelongsToOneChain;

    protected $orderedColumn = 'organization';

    protected $fillable = [
        'chain_id',
        'organization',
        'contact_name',
        'phone'
    ];

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
