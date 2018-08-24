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

    /**
     * Filter the questions by client type.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param string $client_type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForType($query, $client_type)
    {
        if (empty($client_type)) {
            return $query;
        }

        return $query->where(function($q) use ($client_type) {
            $q->where('client_type', $client_type)
                ->orWhereNull('client_type');
        });
    }
}
