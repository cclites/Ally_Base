<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * App\Signature
 *
 * @property int $id
 * @property int $signable_id
 * @property string $signable_type
 * @property mixed $content
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $signable
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Signature whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Signature whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Signature whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Signature whereSignableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Signature whereSignableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Signature whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Signature extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $guarded = ['id'];

    /**
     * Get all of the owning signable models.
     */
    public function signable()
    {
        return $this->morphTo();
    }
    
    public static function onModelInstance(Model $model, $content)
    {
        if ($content) {
            return Signature::create([
                'signable_id' => $model->getKey(),
                'signable_type' => $model->getMorphClass(),
                'content' => $content,
            ]);
        }
        return null;
    }
}
