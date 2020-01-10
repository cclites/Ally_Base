<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VisitEditAction extends Model
{
    protected $guarded = ['id'];

    const NONEVVDEFAULT = 14;


    /**
     * the default action for non-verified shifts
     *
     * @return array
     */
    public static function nonEvvDefault()
    {
        return self::NONEVVDEFAULT;
    }

    public function getFormattedNameAttribute()
    {
        return $this->code . ": " . $this->description;
    }
}
