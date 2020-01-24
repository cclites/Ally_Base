<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VisitEditReason extends Model
{
    protected $guarded = ['id'];

    const NONEVVDEFAULT = 910;

    /**
     * the default action for non-verified shifts
     *
     * @return array
     */
    public static function nonEvvDefault()
    {
        return self::where( 'code', self::NONEVVDEFAULT )->first()->id;
    }

    public function getFormattedNameAttribute()
    {
        return $this->code . ": " . $this->description;
    }
}
