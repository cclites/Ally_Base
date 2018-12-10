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
        'option_value',
    ];
}
