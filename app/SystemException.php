<?php
namespace App;

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

}