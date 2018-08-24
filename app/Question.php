<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Question extends Model
{
    use SoftDeletes;

    /**
     * The attributes that should not be mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * Get the business relation.
     *
     * @return Illuminate/Database/Eloquent/Relations/BelongsTo
     */
    public function business()
    {
        return $this->belongsTo(Business::class);
    }
}
