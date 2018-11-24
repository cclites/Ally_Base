<?php

namespace App;

use App\Contracts\BelongsToBusinessesInterface;
use App\Traits\BelongsToBusinesses;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class OtherContact extends Model implements BelongsToBusinessesInterface
{
    use BelongsToBusinesses;

    /**
     * The fields that are mass assignable
     *
     * @var array
     */
    protected $fillable = [
        'firstname',
        'lastname',
        'business_id',
        'email',
        'title',
        'company',
        'phone',
        'address1',
        'address2',
        'city',
        'state',
        'zip',
        'country',
        'general_notes',
    ];

    /**
     * Return an array of business IDs the entity is attached to
     *
     * @return array
     */
    public function getBusinessIds()
    {
        return [$this->business_id];
    }

    public function getNameAttribute()
    {
        return $this->name();
    }

    public function getNameLastFirstAttribute()
    {
        return $this->nameLastFirst();
    }

    public function name()
    {
        return $this->firstname . ' ' . $this->lastname;
    }

    public function nameLastFirst()
    {
        return $this->lastname . ', ' . $this->firstname;
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Add a query scope "ordered()" to centralize the control of sorting order of model results in queries
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param string|null $direction
     */
    public function scopeOrdered(Builder $builder, string $direction = null)
    {
        $builder->orderBy('lastname', $direction ?? 'ASC')
            ->orderBy('firstname', $direction ?? 'ASC');
    }

    /**
     * A query scope for filtering results by related business IDs
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param array $businessIds
     * @return void
     */
    public function scopeForBusinesses(Builder $builder, array $businessIds)
    {
        $builder->whereIn('business_id', $businessIds);
    }
}
