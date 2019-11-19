<?php
namespace App;

use App\Contracts\BelongsToBusinessesInterface;
use App\Traits\BelongsToOneBusiness;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Question
 *
 * @property int $id
 * @property int $business_id
 * @property string $question
 * @property string|null $client_type
 * @property int $required
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read \App\Business $business
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Question forBusinesses($businessIds)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Question forRequestedBusinesses($businessIds = null, \App\User $authorizedUser = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Question forType($client_type)
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Question onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Question whereBusinessId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Question whereClientType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Question whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Question whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Question whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Question whereQuestion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Question whereRequired($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Question whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Question withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Question withoutTrashed()
 * @mixin \Eloquent
 */
class Question extends AuditableModel implements BelongsToBusinessesInterface
{
    use BelongsToOneBusiness;
    use SoftDeletes;

    /**
     * The attributes that should not be mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * Get the business relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Filter the questions by client type.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $client_type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForType($query, $client_type)
    {
        if (empty($client_type)) {
            return $query;
        }

        return $query->where(function($q) use ($client_type) {
            $q->where('client_type', $client_type)
                ->orWhereNull('client_type');
        });
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
            'question' => $faker->sentence.'?',
        ];
    }
}
