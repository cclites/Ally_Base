<?php

namespace App;

use Crypt;
use Illuminate\Database\Eloquent\Model;
use QuickBooksOnline\API\Core\OAuth\OAuth2\OAuth2AccessToken;

class QuickbooksConnection extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    public $with = [];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [];

    // **********************************************************
    // RELATIONSHIPS
    // **********************************************************

    /**
     * Get the business relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
    */
    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    // **********************************************************
    // MUTATORS
    // **********************************************************

    /**
     * Get and decrypt the access token object.
     *
     * @return mixed|null
     */
    public function getAccessTokenAttribute()
    {
        return empty($this->attributes['access_token']) ? null : unserialize(Crypt::decrypt($this->attributes['access_token']));
    }

    /**
     * Set and encrypt the access token object.
     *
     * @param OAuth2AccessToken|null $value
     */
    public function setAccessTokenAttribute(?OAuth2AccessToken $value)
    {
        $this->attributes['access_token'] = $value ? Crypt::encrypt(serialize($value)) : null;
    }

    // **********************************************************
    // QUERY SCOPES
    // **********************************************************

    // **********************************************************
    // OTHER FUNCTIONS
    // **********************************************************

}
