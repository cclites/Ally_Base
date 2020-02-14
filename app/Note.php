<?php
namespace App;

use App\Contracts\BelongsToBusinessesInterface;
use App\Traits\BelongsToOneBusiness;
use App\Traits\ScrubsForSeeding;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Note
 *
 * @property int $id
 * @property int|null $caregiver_id
 * @property int|null $prospect_id
 * @property int|null $referral_source_id
 * @property int|null $client_id
 * @property string $body
 * @property string|null $tags
 * @property string|null $type
 * @property int $created_by
 * @property int $business_id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read \App\Business $business
 * @property-read \App\Caregiver|null $caregiver
 * @property-read \App\Client|null $client
 * @property-read \App\Client|null $prospect
 * @property-read \App\ReferralSource|null $referral_source
 * @property-read \App\User $creator
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Note forBusinesses($businessIds)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Note forRequestedBusinesses($businessIds = null, \App\User $authorizedUser = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Note whereBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Note whereBusinessId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Note whereCaregiverId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Note whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Note whereProspectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Note whereReferralSourceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Note whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Note whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Note whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Note whereTags($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Note whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Note whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property string|null $title
 * @property string|null $call_direction
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Note whereCallDirection($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Note whereTitle($value)
 * @property int|null $import_id
 * @property-read int|null $audits_count
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Note newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Note newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Note query()
 */
class Note extends AuditableModel implements BelongsToBusinessesInterface
{
    use BelongsToOneBusiness;

    protected $guarded = ['id'];
    protected $orderedColumn = 'id';

    public function business()
    {
        return $this->belongsTo('App\Business');
    }

    public function caregiver()
    {
        return $this->belongsTo('App\Caregiver');
    }

    public function client()
    {
        return $this->belongsTo('App\Client');
    }

    public function prospect()
    {
        return $this->belongsTo('App\Prospect');
    }

    public function referral_source()
    {
        return $this->belongsTo('App\ReferralSource');
    }

    public function creator()
    {
        return $this->belongsTo('App\User', 'created_by');
    }

    public function template(){
        return $this->belongsTo('App\NoteTemplate', 'template_id');
    }

    // **********************************************************
    // ScrubsForSeeding Methods
    // **********************************************************
    use ScrubsForSeeding;

    /**
     * Get an array of scrubbed data to replace the original.
     *
     * @param \Faker\Generator $faker
     * @param bool $fast
     * @param null|Model $item
     * @return array
     */
    public static function getScrubbedData(\Faker\Generator $faker, bool $fast, ?\Illuminate\Database\Eloquent\Model $item) : array
    {
        return [
            'body' => $faker->sentence,
            'title' => $faker->sentence,
            'tags' => $faker->word,
        ];
    }
}
