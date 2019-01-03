<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TriggeredReminder extends Model
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
    
    public $table = 'reminders_triggered';
    
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
    
    /**
     * Look up record by notifiation and reference id.
     *
     * @param \Illuminate\Database\Query\Builder query
     * @param string $notification
     * @param int $reference
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeForReminder($query, $notification, $reference)
    {
        return $query->where('notification', $notification)
                ->where('reference_id', $reference);
    }

    // **********************************************************
    // OTHER FUNCTIONS
    // **********************************************************
}
