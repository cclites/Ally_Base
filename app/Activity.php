<?php

namespace App;

use App\Contracts\BelongsToBusinessesInterface;
use App\Traits\BelongsToOneBusiness;
use Illuminate\Database\Eloquent\Builder;

/**
 * App\Activity
 *
 * @property int $id
 * @property int|null $business_id
 * @property string|null $code
 * @property string|null $name
 * @property string|null $description
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read \App\Business|null $business
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\CarePlan[] $carePlans
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Activity forBusinesses($businessIds)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Activity forRequestedBusinesses($businessIds = null, \App\User $authorizedUser = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Activity whereBusinessId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Activity whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Activity whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Activity whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Activity whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Activity whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Activity whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property-read int|null $audits_count
 * @property-read int|null $care_plans_count
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Activity newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Activity newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Activity query()
 */
class Activity extends AuditableModel implements BelongsToBusinessesInterface
{
    use BelongsToOneBusiness;

    protected $table = 'activities';
    protected $guarded = ['id'];
    protected $orderedColumn = 'code';

    ///////////////////////////////////////////
    /// Relationship Methods
    ///////////////////////////////////////////

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function carePlans()
    {
        return $this->belongsToMany(CarePlan::class, 'care_plan_activities');
    }

    ////////////////////////////////////
    //// Query Scopes
    ////////////////////////////////////

    /**
     * A query scope for filtering results by related business IDs
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param array $businessIds
     * @return void
     */
    public function scopeForBusinesses(Builder $builder, array $businessIds)
    {
        $builder->where(function($q) use ($businessIds) {
            $q->whereIn('business_id', $businessIds)
                ->orWhereNull('business_id');
        });
    }
}
