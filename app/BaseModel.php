<?php
namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

abstract class BaseModel extends Model
{
    /**
     * @var string  The column to sort by default when using ordered()
     */
    protected $orderedColumn;

    /**
     * @var string  The direction to sort by default when using ordered()
     */
    protected $orderedDir = 'ASC';

    /**
     * Add a query scope "ordered()" to centralize the control of sorting order of model results in queries
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param string|null $direction
     */
    public function scopeOrdered(Builder $builder, string $direction = null)
    {
        if (is_string($this->orderedColumn)) {
            $builder->orderBy($this->orderedColumn, $direction ?? $this->orderedDir ?? 'ASC');
        }
    }
}