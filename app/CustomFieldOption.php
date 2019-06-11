<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CustomFieldOption extends Model
{   
    /**
     * The database table associated with this model
     * 
     * @var string
     */
    protected $table = 'business_custom_field_options';

    /**
     * The attributes that are mass assignable
     *
     * @var array
     */
    protected $fillable = [
        'field_id',
        'value',
        'label',
    ];
    
    /**
     * The custom model attributes to add to the Eloquent model
     *
     * @var array
     */
    protected $appends = ['text'];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();
        
        // Delete all custom field values
        self::deleting(function($option) {
            $field = $option->field;
            $field->caregivers->each(function($meta) {
                $meta->delete();
            });
 
            $field->clients->each(function($meta) {
                $meta->delete();
            });
        });
    }

    // **********************************************************
    // RELATIONSHIPS
    // **********************************************************

    /**
     * Get the custom dropdown field that this option belongs to
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function field()
    {
        return $this->belongsTo(CustomField::class);
    }

    // **********************************************************
    // MUTATORS
    // **********************************************************

    /**
     * Create an alias for the label attribute to simplify usage of custom field options in the front end
     *
     * @return string
     */
    public function getTextAttribute()
    {
        return $this->label;
    }

    // **********************************************************
    // QUERY SCOPES
    // **********************************************************

    // **********************************************************
    // OTHER FUNCTIONS
    // **********************************************************

    /**
     * Get the value for a list option from it's label.
     *
     * @param string $label
     * @return string
     */
    public static function getValueFromLabel(string $label) : string
    {
        return snake_case(preg_replace('/[^A-Za-z0-9]/', '', $label));
    }

    /**
     * Get the IDs of all options that do not have labels present
     * in the given array.
     *
     * @param \App\CustomField $customField
     * @param array $labels
     * @return array
     */
    public static function findMissingIds(CustomField $customField, array $labels) : array
    {
        $values = collect($labels)->map(function ($item) {
            return self::getValueFromLabel($item);
        });

        return $customField->options()
            ->whereNotIn('value', $values)
            ->pluck('id')
            ->toArray();
    }
}
