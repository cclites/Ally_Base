<?php

namespace App;

use App\CustomField;
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
     * Create an alias for the label attribute to simplify usage of custom field options in the front end
     *
     * @return string
     */
    public function getTextAttribute()
    {
        return $this->label;
    }

    /**
     * Get the custom dropdown field that this option belongs to
     *
     * @return \App\CustomField
     */
    public function field()
    {
        return $this->belongsTo(CustomField::class);
    }
}
