<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Contracts\BelongsToChainsInterface;
use App\Traits\BelongsToOneChain;

class CustomField extends BaseModel implements BelongsToChainsInterface
{
    use BelongsToOneChain;

    /**
     * @var string  The column to sort by default when using ordered()
     */
    protected $orderedColumn = 'key';

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
        self::deleting(function(CustomField $field) {
            if($field->isDropdown()) {
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

    // **********************************************************
    // RELATIONSHIPS
    // **********************************************************

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
        return $this->hasMany(CustomFieldOption::class, 'field_id')
            ->ordered();
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
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function clients()
    {
        return $this->hasMany(ClientMeta::class, 'key', 'key');
    }

    // **********************************************************
    // MUTATORS
    // **********************************************************

    /**
     * Get the displayable value of the default for this custom field
     *
     * @return string
     */
    public function getDefaultAttribute()
    {
        if ($this->isDropdown() && $this->default_value) {
            return $this->options->where('value', $this->default_value)->first()->label;
        }

        return $this->default_value ?: '';
    }

    // **********************************************************
    // QUERY SCOPES
    // **********************************************************

    /**
     *
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param int|BusinessChain $chain
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeForChain($query, $chain)
    {
        $id = $chain;
        if (is_object($chain)) {
            $id = $chain->id;
        }

        return $query->where('chain_id', $id);
    }

    /**
     *
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeForClients($query)
    {
        return $query->where('user_type', 'client');
    }

    /**
     *
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeForCaregivers($query)
    {
        return $query->where('user_type', 'caregiver');
    }

    // **********************************************************
    // STATIC FUNCTIONS
    // **********************************************************

    /**
     * Check for duplicate CustomField.
     *
     * @param \App\BusinessChain $chain
     * @param string $userType
     * @param string $label
     * @param string $key
     * @param null|string $ignoreId
     * @return bool
     */
    public static function findDuplicate(BusinessChain $chain, string $userType, string $label, string $key, string $ignoreId = null) : bool
    {
        $query = self::forChain($chain)
            ->where('user_type', $userType)
            ->where(function ($query) use ($label, $key) {
                $query->where('label', $label)
                    ->orWhere('key', $key);
            });

        if (filled($ignoreId)) {
            $query->whereNotIn('id', [$ignoreId]);
        }

        return $query->exists();
    }

    // **********************************************************
    // OTHER FUNCTIONS
    // **********************************************************

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
     * Check if the custom field is a dropdown field.
     *
     * @return bool
     */
    public function isDropdown() : bool
    {
        return $this->type == 'dropdown';
    }
}
