<?php

namespace App;

/**
 * App\ClientPreferences
 *
 * @property int $id
 * @property string|null $gender
 * @property string|null $language
 * @property string|null $license
 * @property int|null $minimum_rating
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientPreferences whereGender($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientPreferences whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientPreferences whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientPreferences whereLicense($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientPreferences whereMinimumRating($value)
 * @mixin \Eloquent
 */
class ClientPreferences extends AuditableModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'client_preferences';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

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
    public $with = ['ethnicities'];

    // **********************************************************
    // RELATIONSHIPS
    // **********************************************************

    /**
     * Get the ethnicities relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
    */
    public function ethnicities()
    {
        return $this->hasMany(ClientEthnicityPreference::class, 'client_id');
    }

    // **********************************************************
    // MUTATORS
    // **********************************************************

    // **********************************************************
    // QUERY SCOPES
    // **********************************************************

    // **********************************************************
    // OTHER FUNCTIONS
    // **********************************************************
}
