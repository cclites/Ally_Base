<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\ServiceProvider;

class MacroServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->addBcSumToCollection();
        $this->addJoinSubToQueryBuilder();
    }

    /**
     * \Illuminate\Support\Collection->bcsum
     *
     * This replicates the behavior of the normal sum() method
     * except it uses our bc math addition helper method to
     * insure there are no floating point issues.
     */
    public function addBcSumToCollection() : void
    {
        \Illuminate\Support\Collection::macro('bcsum', function ($callback) {
            if (is_null($callback)) {
                return $this->reduce(function ($result, $item) {
                    return add($result, floatval($item));
                }, floatval(0.00));
            }

            $callback = $this->valueRetriever($callback);

            return $this->reduce(function ($result, $item) use ($callback) {
                return add($result, floatval($callback($item)));
            }, floatval(0.00));
        });
    }

    public function addJoinSubToQueryBuilder() : void
    {
        Builder::macro('parseSub', function ($query) {
            if ($query instanceof self || $query instanceof EloquentBuilder) {
                return [$query->toSql(), $query->getBindings()];
            } elseif (is_string($query)) {
                return [$query, []];
            } else {
                throw new InvalidArgumentException;
            }
        });

        Builder::macro('createSub', function ($query) {
            // If the given query is a Closure, we will execute it while passing in a new
            // query instance to the Closure. This will give the developer a chance to
            // format and work with the query before we cast it to a raw SQL string.
            if ($query instanceof Closure) {
                $callback = $query;

                $callback($query = $this->forSubQuery());
            }

            return $this->parseSub($query);
        });

        Builder::macro('joinSub', function ($query, $as, $first, $operator = null, $second = null, $type = 'inner', $where = false) {
            [$query, $bindings] = $this->createSub($query);

            $expression = '('.$query.') as '.$this->grammar->wrapTable($as);

            $this->addBinding($bindings, 'join');

            return $this->join(new Expression($expression), $first, $operator, $second, $type, $where);
        });

    }
}
