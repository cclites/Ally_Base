<?php
namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class SystemException extends Model
{
    protected $guarded = ['id'];

    ///////////////////////////////////////////
    /// Relationship Methods
    ///////////////////////////////////////////

    public function acknowledger()
    {
        return $this->belongsTo(User::class, 'acknowledged_by');
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function reference()
    {
        return $this->morphTo('reference');
    }

    ///////////////////////////////////////////
    /// Other Methods
    ///////////////////////////////////////////

    public function acknowledge($note = '', $user_id = null)
    {
        if (!$user_id) $user_id = \Auth::id();
        return $this->update([
            'acknowledged_at' => Carbon::now(),
            'acknowledged_by' => $user_id,
            'notes' => $note,
        ]);
    }

}