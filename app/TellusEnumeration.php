<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\TellusEnumeration
 *
 * @property int $id
 * @property string $category
 * @property int $code
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TellusEnumeration newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TellusEnumeration newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TellusEnumeration query()
 * @mixin \Eloquent
 */
class TellusEnumeration extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tellus_enumerations';

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

}
