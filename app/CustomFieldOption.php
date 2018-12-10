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
        'option_value',
    ];

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
