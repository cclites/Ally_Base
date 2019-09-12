<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Signature
 *
 * @property int $id
 * @property int $signable_id
 * @property string $signable_type
 * @property mixed $content
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $signable
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Signature whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Signature whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Signature whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Signature whereSignableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Signature whereSignableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Signature whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Signature extends AuditableModel
{
    protected $guarded = ['id'];

    /**
     * Get all of the owning signable models.
     */
    public function signable()
    {
        return $this->morphTo();
    }
    
    public static function attachToModel(Model $model, $content, $type = null )
    {
        if ($content) {
            return Signature::create([
                'signable_id'   => $model->getKey(),
                'signable_type' => $model->getMorphClass(),
                'content'       => $content,
                'meta_type'     => $type
            ]);
        }
        return null;
    }
}
