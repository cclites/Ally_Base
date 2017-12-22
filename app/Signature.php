<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Signature extends Model
{
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
