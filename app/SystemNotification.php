<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SystemNotification extends AuditableModel
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
    
    // **********************************************************
    // RELATIONSHIPS
    // **********************************************************
    
    public function reference()
    {
        return $this->morphTo('reference');
    }

    // **********************************************************
    // MUTATORS
    // **********************************************************
    
    // **********************************************************
    // QUERY SCOPES
    // **********************************************************
    
    public function scopeNotAcknowledged($query)
    {
        return $query->whereNull('acknowledged_at');
    }

    // **********************************************************
    // OTHER FUNCTIONS
    // **********************************************************

    /**
     * Set notification as acknowledged.
     *
     * @param string $note
     * @return bool
     */
    public function acknowledged($note = null)
    {
        return $this->update([
            'acknowledged_at' => Carbon::now(),
            'notes' => $note,
        ]);
    }
}
