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
 */
class ClientPreferences extends AuditableModel
{
    protected $table = 'client_preferences';
    protected $guarded = ['id'];
    public $incrementing = false;
    public $timestamps = false;
}
