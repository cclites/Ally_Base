<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * App\CustomFieldOption
 *
 * @property int $id
 * @property int $field_id
 * @property string $value
 * @property string $label
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\CustomField $field
 * @property-read string $text
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CustomFieldOption newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CustomFieldOption newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CustomFieldOption query()
 * @mixin \Eloquent
 */
class CustomFieldOption extends BaseModel
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
     * @var string  The column to sort by default when using ordered()
     */
    protected $orderedColumn = 'label';

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
        return Str::snake(preg_replace('/[^A-Za-z0-9]/', '', $label));
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
