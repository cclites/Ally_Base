<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\ClientEthnicityPreference
 *
 * @property int $id
 * @property int $client_id
 * @property string $ethnicity
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientEthnicityPreference newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientEthnicityPreference newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientEthnicityPreference query()
 * @mixin \Eloquent
 */
class ClientEthnicityPreference extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'client_ethnicity_preferences';

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
