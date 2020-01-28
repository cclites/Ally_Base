<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\TellusTypecode
 *
 * @property int $id
 * @property string $category
 * @property string|null $subcategory
 * @property int $code
 * @property string $text_code
 * @property string $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TellusTypecode newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TellusTypecode newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TellusTypecode query()
 * @mixin \Eloquent
 */
class TellusTypecode extends Model
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

}
