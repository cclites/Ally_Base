<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\VisitEditAction
 *
 * @property int $id
 * @property int $code
 * @property string $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $formatted_name
 * @method static \Illuminate\Database\Eloquent\Builder|\App\VisitEditAction newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\VisitEditAction newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\VisitEditAction query()
 * @mixin \Eloquent
 */
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
        return self::where( 'code', self::NONEVVDEFAULT )->first()->id;
    }

    public function getFormattedNameAttribute()
    {
        return $this->code . ": " . $this->description;
    }
}
