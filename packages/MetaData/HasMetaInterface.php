<?php
namespace Packages\MetaData;


use Illuminate\Support\Collection;

/**
 * Interface HasMetaInterface implemented by HasMetaData trait
 *
 * @package Packages\MetaData
 * @author Devon Bessemer
 */
interface HasMetaInterface
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function meta();

    /**
     * Retrieve all the meta data for this model matching the provided keys
     *
     * @param string|array $keys
     * @param int|null $limit
     * @return \Illuminate\Support\Collection|\Packages\MetaData\MetaData[]
     */
    public function getMetaData($keys = null, ?int $limit = null): Collection;

    /**
     * Returns the first value found from a key
     *
     * @param string $key
     * @return string|null
     */
    public function getMetaValue(string $key): ?string;

    /**
     * Updates, or creates, a single meta key
     *
     * @param string $key
     * @param string|null $value
     * @return bool
     * @throws \Packages\MetaData\Exceptions\ModelNotSavedException
     */
    public function setMeta(string $key, ?string $value): bool;

    /**
     * Adds a new key=>value pair, supports multiple of the same key per user
     *
     * @param string $key
     * @param string|null $value
     * @return bool
     * @throws \Packages\MetaData\Exceptions\ModelNotSavedException
     */
    public function addMeta(string $key, ?string $value): bool;

    /**
     * Adds the whereMeta scope to the query builder
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|callable $key If a string, the meta key to search. If callable, it will be passed as the whereHas query callable.
     * @param mixed|null $delimiter
     * @param mixed|null $value
     *
     * @return void
     */
    public function scopeWhereMeta($query, $key, $delimiter = null, $value = null);

    /**
     * Adds a shortcut for eager loading meta data via withMeta()
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return void
     */
    public function scopeWithMeta($query);
}