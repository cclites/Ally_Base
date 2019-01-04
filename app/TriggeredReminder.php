<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TriggeredReminder extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    public $table = 'reminders_triggered';

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
    // STATIC METHODS
    // **********************************************************

    /**
     * Get records of reminders that have been triggered.
     *
     * @param string $key
     * @param array $reference_ids
     * @return mixed
     */
    public static function getTriggered($key, $reference_ids)
    {
        return TriggeredReminder::where('key', $key)
            ->whereIn('reference_id', $reference_ids)
            ->get()
            ->pluck('reference_id');
    }

    /**
     * @param string $key
     * @param int $reference_id
     * @return mixed
     */
    public static function markTriggered($key, $reference_id)
    {
        return TriggeredReminder::create([
            'reference_id' => $reference_id,
            'key' => $key,
        ]);
    }
}
