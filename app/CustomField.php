<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\BusinessChain;
use App\CustomFieldOption;
use App\CaregiverMeta;
use App\ClientMeta;
use App\Contracts\BelongsToChainsInterface;
use App\Traits\BelongsToOneChain;
use Illuminate\Database\Eloquent\Builder;

class CustomField extends Model implements BelongsToChainsInterface
{
    use BelongsToOneChain;

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
     * The custom model attributes to add to the Eloquent model
     * 
     * @var array
     */
    protected $appends = [
        'default',
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

                $field->caregivers->each(function($meta) {
                    $meta->delete();
                });
    
                $field->clients->each(function($meta) {
                    $meta->delete();
                });
            }
        });
    }

    /**
     * Get the displayable value of the default for this custom field
     *
     * @return string
     */
    public function getDefaultAttribute()
    {
        if($this->type == 'dropdown' && $this->default_value) {
            return $this->options->where('value', $this->default_value)->first()->label;
        }

        return $this->default_value ?: '';
    }

    /**
     * Get the business chain that this field was created for
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function businessChain()
    {
        return $this->belongsTo(BusinessChain::class, 'chain_id');
    }

    /**
     * Get the dropdown custom fields options associated with this field
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function options()
    {
        return $this->hasMany(CustomFieldOption::class, 'field_id');
    }

    /**
     * Get the value of the custom field for the caregivers who have set one
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function caregivers()
    {
        return $this->hasMany(CaregiverMeta::class, 'key', 'key');
    }

    /**
     * Get the value of the custom field for the clients who have set one
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function clients()
    {
        return $this->hasMany(ClientMeta::class, 'key', 'key');
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
}
