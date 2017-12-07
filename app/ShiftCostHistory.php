<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class ShiftCostHistory extends Model
{
    protected $table = 'shift_cost_history';
    protected $guarded = [];

    public function shift()
    {
        return $this->belongsTo(Shift::class, 'id');
    }
}