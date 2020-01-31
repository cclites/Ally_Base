<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\AdminPin
 *
 * @property int $id
 * @property int $pin
 * @property string $name
 * @property string $access
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AdminPin newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AdminPin newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AdminPin query()
 * @mixin \Eloquent
 */
class AdminPin extends Model
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
    // RELATIONSHIPS
    // **********************************************************

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
     * Verify access using specified pin.
     *
     * @param string $pin
     * @param string $access
     * @return mixed
     */
    public static function verify(string $pin, string $access)
    {
        return AdminPin::where('pin', '=', $pin)
            ->where('access', '=', $access)
            ->exists();
    }
    
    // **********************************************************
    // ScrubsForSeeding Methods
    // **********************************************************
    use \App\Traits\ScrubsForSeeding;
    
    /**
     * Get an array of scrubbed data to replace the original.
     *
     * @param \Faker\Generator $faker
     * @param bool $fast
     * @param null|\Illuminate\Database\Eloquent\Model $item
     * @return array
     */
    public static function getScrubbedData(\Faker\Generator $faker, bool $fast, ?\Illuminate\Database\Eloquent\Model $item) : array
    {
        return [
            'pin' => '1234',
        ];
    }
}
