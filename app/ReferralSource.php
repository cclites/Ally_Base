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

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'chain_id',
        'organization',
        'contact_name',
        'phone',
        'type',
        'is_company',
        'source_owner',
        'source_type',
        'web_address',
        'work_phone',
        'active'
    ];
    
    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    public $with = [];
    
    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [];
    
    /**
     * The default sorting column.
     *
     * @var string
     */
    protected $orderedColumn = 'organization';

    // **********************************************************
    // REFERRAL SOURCE TYPES
    // **********************************************************
    const TYPE_CLIENT = 'client';
    const TYPE_CAREGIVER = 'caregiver';

    // **********************************************************
    // RELATIONSHIPS
    // **********************************************************
    
    /**
     * A ReferralSource can have many Caregivers.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function caregivers() {
        return $this->hasMany(Caregiver::class);
    }

    /**
     * A ReferralSource can have many Clients.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function clients() {
        return $this->hasMany(Client::class);
    }

    /**
     * A ReferralSource can have many Prospects.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function prospects() {
        return $this->hasMany(Prospect::class);
    }

    /**
     * Get the referral source notes relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function notes()
    {
        return $this->hasMany(Note::class);
    }

    // **********************************************************
    // MUTATORS
    // **********************************************************
    public static function orderResources($referralsources){
        $set = [];
        $cnt = 0;

        foreach($referralsources as $item)
        {
            $key = $item['organization'];

            if(!isset($set[$key])){
                $set[$key]['organization'] = $item['organization'];

                if($item['active']){
                    $set[$key]['contact_name'] = $item['contact_name'] . ", ";
                }else{
                    $set[$key]['contact_name'] = '';
                }
                $set[$key]['contacts'][] = ['contact_name'=>$item['contact_name'], 'id'=>$item['id'], 'phone'=>$item['phone'], 'active'=>$item['active']];
                $set[$key]['phone'] = $item['phone'];
                $set[$key]['id'] = $cnt++;
                $set[$key]['source_id'] = $item[ 'id' ];
                $set[$key]['created_at'] = $item['created_at']->format('m/d/Y');
                $set[$key]['source_type'] = $item[ 'source_type' ];
                $set[$key]['source_owner'] = $item[ 'source_owner' ];
                $set[$key]['web_address'] = $item[ 'web_address' ];
                $set[$key]['is_company'] = $item[ 'is_company' ];
                $set[$key]['work_phone'] = $item[ 'work_phone' ];
            }else{
                if($item['active']){
                    $set[$key]['contact_name'] .= $item['contact_name'] . ", ";
                }
                $set[$key]['contacts'][] = ['contact_name'=>$item['contact_name'], 'id'=>$item['id'], 'phone'=>$item['phone'], 'active'=>$item['active']];
            }
        }

        $data = [];

        foreach($set as $key=>$value){
            $value['contact_name'] = rtrim($value['contact_name'], ', ');
            $data[] = $value;
        }

        return json_encode($data);
    }
    
    // **********************************************************
    // QUERY SCOPES
    // **********************************************************
    
    /**
     * Get only sources for the given type.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param null|string $type
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeForType($query, ?string $type = null)
    {
        if (empty($type)) {
            return $query;
        }

        return $query->where('type', $type);
    }

    // **********************************************************
    // OTHER FUNCTIONS
    // **********************************************************

    /**
     * Get a list of the valid ReferralSource types.
     *
     * @return array
     */
    public static function validTypes() : array
    {
        return [static::TYPE_CLIENT, static::TYPE_CAREGIVER];
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
            'organization' => $faker->company,
            'source_owner' => $faker->company,
            'contact_name' => $faker->name,
            'phone' => $faker->simple_phone,
            'web_address' => $faker->url,
            'work_phone' => $faker->simple_phone,
        ];
    }
}
