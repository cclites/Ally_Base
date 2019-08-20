<?php

namespace App;

class SkilledNursingPoc extends AuditableModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'skilled_nursing_pocs';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];


    // **********************************************************
    // RELATIONSHIPS
    // **********************************************************

    /**
     * Get the client relationship.
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

    public function getMobilityAttribute()
    {
        return self::stringToArray($this->attributes['mobility']);
    }

    public function getFunctionalAttribute()
    {
        return self::stringToArray($this->attributes['functional']);
    }

    public function getMentalStatusAttribute()
    {
        return self::stringToArray($this->attributes['mental_status']);
    }


    // **********************************************************
    // OTHER FUNCTIONS
    // **********************************************************

    /**
     * Attributes that are booleans.
     *
     * @var array
     */
    protected static $boolKeys = [];

    /**
     * Attributes that are imploded arrays.
     *
     * @var array
     */
    protected static $arrayKeys = [
        'safety_measures',
        'mobility',
        'diet',
        'skin',
        'oral',
        'nails',
        'dressing',
        'housekeeping',
        'errands',
        'supplies',
        'mental_status'
    ];

    /**
     * Converts form data array into mass assignable array.
     *
     * @param array $data
     * @return array
     */
    public static function convertFormData($data)
    {
        // fix boolean values
        foreach (self::$boolKeys as $key) {
            $data[$key] = $data[$key] ?? false;
        }

        // implode all array fields
        foreach (self::$arrayKeys as $key) {
            $data[$key] = self::arrayToString($data[$key]);
        }

        return $data;
    }

    /**
     * Explodes string into an array.
     *
     * @param string $value
     * @return array
     */
    public static function stringToArray($value)
    {
        if (empty($value)) {
            return [];
        }

        return explode(',', $value);
    }

    /**
     * Implodes array into string.
     *
     * @param array $value
     * @return null|string
     */
    public static function arrayToString($value)
    {
        if (empty($value)) {
            return null;
        }

        return implode(',', $value);
    }
}
