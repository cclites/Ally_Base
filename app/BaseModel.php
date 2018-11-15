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
     */
    public function scopeOrdered(Builder $builder)
    {
        if ($this->orderedColumn) {
            $builder->orderBy($this->orderedColumn, $this->orderedDir);
        }
    }
}