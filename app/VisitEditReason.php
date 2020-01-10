<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VisitEditReason extends Model
{
    protected $guarded = ['id'];

    const NONEVVVDEFAULT = 910;

    /**
     * the default action for non-verified shifts
     *
     * @return array
     */
    public static function nonEvvDefault()
    {
        return self::NONEVVVDEFAULT;
    }


    public function getFormattedNameAttribute()
    {
        return $this->code . ": " . $this->description;
    }
}
