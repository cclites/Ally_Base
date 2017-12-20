<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class ShiftStatusHistory extends Model
{
    protected $table = 'shift_status_history';
    protected $guarded = ['id'];
}