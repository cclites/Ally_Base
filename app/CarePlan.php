<?php
namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CarePlan extends Model
{
    use SoftDeletes;

    protected $table = 'care_plans';
    protected $guarded = ['id'];

    ///////////////////////////////////////////
    /// Relationship Methods
    ///////////////////////////////////////////

    public function activities()
    {
        return $this->belongsToMany(Activity::class, 'care_plan_activities');
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }
}