<?php

namespace App;

use App\Billing\Service;
use Illuminate\Database\Eloquent\Model;

class ClaimableService extends Model
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

    public function shift()
    {
        return $this->belongsTo( Shift::class );
    }

    public function caregiver()
    {
        return $this->belongsTo( Caregiver::class );
    }

    public function service()
    {
        return $this->belongsTo( Service::class );
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
