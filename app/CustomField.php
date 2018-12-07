<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\BusinessChain;

class CustomField extends Model
{
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
     * Get the business chain that this field was created for
     *
     * @return \App\BusinessChain
     */
    public function businessChain()
    {
        return $this->belongsTo(BusinessChain::class, 'chain_id');
    }
}
