<?php

namespace App;

use App\Billing\Claim;
use Illuminate\Database\Eloquent\Model;

class TellusFile extends Model
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
        parent::boot();
    }

    const STATUS_PENDING = 'pending';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_REJECTED = 'rejected';

    // **********************************************************
    // RELATIONSHIPS
    // **********************************************************

    /**
     * Get the Claim relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
    */
    public function claim()
    {
        return $this->belongsTo(Claim::class);
    }

    /**
     * Get the results relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
    */
    public function results()
    {
        return $this->hasMany(TellusFileResult::class, 'tellus_file_id', 'id');
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
