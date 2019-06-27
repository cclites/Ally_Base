<?php

namespace App;

class SkilledNursingPoc extends AuditableModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'client_care_details';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    const PETS = [
        self::PET_CATS,
        self::PET_DOGS,
        self::PET_BIRDS,
        self::PET_OTHER,
    ];

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

    public function getPetsAttribute()
    {
        return self::stringToArray($this->attributes['pets']);
    }

    // **********************************************************
    // OTHER FUNCTIONS
    // **********************************************************

    /**
     * Attributes that are booleans.
     *
     * @var array
     */
    protected static $boolKeys = ['lives_alone'];

    /**
     * Attributes that are imploded arrays.
     *
     * @var array
     */
    protected static $arrayKeys = [
        'pets',
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
