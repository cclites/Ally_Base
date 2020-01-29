<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Carbon;

/**
 * App\TriggeredReminder
 *
 * @property int $id
 * @property string $key
 * @property int $reference_id
 * @property string $expires_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TriggeredReminder newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TriggeredReminder newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TriggeredReminder query()
 * @mixin \Eloquent
 */
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
     * @param array|Collection $reference_ids
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
    public static function markTriggered($key, $reference_id, $expireDate = null)
    {
        if (empty($expireDate)) {
            $expireDate = Carbon::now()->addDays(2);
        }

        return TriggeredReminder::create([
            'reference_id' => $reference_id,
            'key' => $key,
            'expires_at' => $expireDate,
        ]);
    }
}
