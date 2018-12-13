<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\BusinessChain;
use App\CustomFieldOption;
use App\Contracts\BelongsToChainsInterface;
use App\Traits\BelongsToBusinesses;
use App\Traits\BelongsToOneChain;
use App\Contracts\BelongsToBusinessesInterface;
use Illuminate\Database\Eloquent\Builder;

class CustomField extends Model implements BelongsToBusinessesInterface, BelongsToChainsInterface
{
    use BelongsToBusinesses, BelongsToOneChain;

    /**
     * The database table associated with this model
     * 
     * @var string
     */
    protected $table = 'business_custom_fields';

    /**
     * The attributes that are mass assignable
     * 
     * @var array
     */
    protected $fillable = [
        'chain_id',
        'key',
        'label',
        'user_type',
        'type',
        'required',
        'default_value',
    ];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();
        
        // Delete all related options for dropdown
        self::deleting(function($field) {
            if($field->type == 'dropdown') {
                $field->options->each(function($option) {
                    $option->delete();
                });
            }
        });
    }

    /**
     * Get the business chain that this field was created for
     *
     * @return \App\BusinessChain
     */
    public function businessChain()
    {
        return $this->belongsTo(BusinessChain::class, 'chain_id');
    }

    /**
     * Get the dropdown custom fields options associated with this field
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function options()
    {
        return $this->hasMany(CustomFieldOption::class, 'field_id');
    }

    /**
     * Return an array of business IDs the entity is attached to
     *
     * @return array
     */
    public function getBusinessIds()
    {
        return $this->businessChain->businesses->pluck('id')->toArray();
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
        $builder->whereIn('id', $businessIds);
    }
}
