<?php
namespace App\Billing\Queries;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Arr;

abstract class BaseQuery extends Builder
{
    public function __construct()
    {
        parent::__construct($this->newBaseQueryBuilder());
        $model = $this->getModelInstance();
        $this->setModel($model);
        $model->registerGlobalScopes($this);
    }

    /**
     * Return the underlying query builder instance
     *
     * @return \Illuminate\Database\Query\Builder
     */
    function getQueryBuilder()
    {
        return $this->query;
    }

    /**
     * Renew the query builder to clear existing query parameters
     *
     * @return $this
     */
    function newQuery(): self
    {
        $this->query = $this->newBaseQueryBuilder();
        $this->setModel($this->getModelInstance());

        return $this;
    }

    /**
     * Get a new query builder instance for the connection.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    protected function newBaseQueryBuilder()
    {
        $connection = $this->getModelInstance()->getConnection();

        return new QueryBuilder(
            $connection, $connection->getQueryGrammar(), $connection->getPostProcessor()
        );
    }

    /**
     * Call the parent method to retrieve query results and reset query builder instance for the next independent query
     *
     * @param array $columns
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    function get($columns = ['*'])
    {
        $results = parent::get($columns);
        $this->newQuery();
        return $results;
    }

    /**
     * Reset the query instance on any execution methods passed through to the QueryBuilder class
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        $resetOnMethods = Arr::except($this->passthru, ['exists', 'doesntExist', 'getConnection']);
        $result = parent::__call($method, $parameters);
        if (in_array($method, $resetOnMethods)) {
            $this->newQuery();
        }
        return $result;
    }

    /**
     * Return an empty instance of the Model this class queries
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    abstract function getModelInstance(): Model;
}