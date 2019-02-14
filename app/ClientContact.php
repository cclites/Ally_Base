<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ClientContact extends Model
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
    
    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        //
        parent::boot();
    }
    
    // **********************************************************
    // Contact Relationship Options
    // **********************************************************
    
    const RELATION_FAMILY = 'family';
    const RELATION_POA = 'poa';
    const RELATION_PHYSICIAN = 'physician';
    const RELATION_OTHER = 'other';
    const RELATION_CUSTOM = 'custom';

    // **********************************************************
    // RELATIONSHIPS
    // **********************************************************
    
    /**
     * Get the Client relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
    */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    // **********************************************************
    // MUTATORS
    // **********************************************************
    
    /**
     * Get the contact's first name.
     *
     * @return string
     */
    public function getFirstNameAttribute()
    {
        if (strpos($this->name, ' ') >= 0) {
            return substr($this->name, 0, strpos($this->name, ' '));
        }

        return $this->name;
    }

    /**
     * Get the contact's last name.
     *
     * @return string
     */
    public function getLastNameAttribute()
    {
        if (strpos($this->name, ' ') >= 0) {
            return substr($this->name, strpos($this->name, ' ') + 1);
        }

        return null;
    }

    // **********************************************************
    // QUERY SCOPES
    // **********************************************************
    
    // **********************************************************
    // OTHER FUNCTIONS
    // **********************************************************

    /**
     * Get the valid relationship values.
     *
     * @return array
     */
    public static function validRelationships() : array
    {
        return [
            self::RELATION_CUSTOM,
            self::RELATION_FAMILY,
            self::RELATION_PHYSICIAN,
            self::RELATION_POA,
            self::RELATION_OTHER,
        ];
    }
}
