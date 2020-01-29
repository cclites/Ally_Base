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
 * @property int $smokes
 * @property int $pets_dogs
 * @property int $pets_cats
 * @property int $pets_birds
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientPreferences newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientPreferences newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientPreferences query()
 * @property-read int|null $audits_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\ClientEthnicityPreference[] $ethnicities
 * @property-read int|null $ethnicities_count
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

    /**
     * Get string array of the client's ethnicity preferences.
     *
     * @return array
     */
    public function getEthnicities() : array
    {
        if (empty($this->ethnicities) || $this->ethnicities->count() === 0) {
            // There is no reason for a client to have zero ethnicity
            // preferences so always return all values by default.
            return array_values(Ethnicity::toArray());
        }

        return $this->ethnicities->pluck('ethnicity')->toArray();
    }
}
