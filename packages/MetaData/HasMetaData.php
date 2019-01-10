<?php
namespace Packages\MetaData;

use Illuminate\Support\Collection;
use Packages\MetaData\Exceptions\ModelNotSavedException;

/**
 * Trait HasMetaData implements HasMetaInterface
 *
 * @package Packages\MetaData
 * @author Devon Bessemer
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait HasMetaData
{
    ///////////////////////////////////////////
    ///  Relationship Method(s)
    ///////////////////////////////////////////

    public function meta()
    {
        if ($class = $this->getOwnMetaClass()) {
            return $this->hasMany($class);
        }
        return $this->morphMany(MetaData::class, 'metable');
    }

    /**
     * Implement this function to return a specific meta model name if not using the global metadata table
     * @return null|string
     */
    public function getOwnMetaClass()
    {
        return null;
    }

    ///////////////////////////////////////////
    /// Instance Method(s)
    ///////////////////////////////////////////

    /**
     * Retrieve all the meta data for this model matching the provided keys
     *
     * @param string|array $keys
     * @param int|null $limit
     * @return \Illuminate\Support\Collection|\Packages\MetaData\MetaData[]
     */
    public function getMetaData($keys = null, ?int $limit = null): Collection
    {
        $query = $this->meta()->select(['key', 'value']);

        if ($keys) {
            $query->whereIn('key', (array) $keys);
        }

        if ($limit > 0) {
            $query->limit($limit);
        }

        return $query->get();
    }

    /**
     * Returns the first value found from a key
     *
     * @param string $key
     * @return string|null
     */
    public function getMetaValue(string $key): ?string
    {
        $meta = $this->getMeta($key, 1)->first();
        return $meta ? (string) $meta->value : null;
    }

    /**
     * Updates, or creates, a single meta key
     *
     * @param string $key
     * @param string|null $value
     * @return bool
     * @throws \Packages\MetaData\Exceptions\ModelNotSavedException
     */
    public function setMeta(string $key, ?string $value): bool
    {
        if ($existing = $this->meta()->where('key', $key)->first()) {
            return $existing->update(['value' => $value]);
        }

        return $this->addMeta($key, $value);
    }

    /**
     * Adds a new key=>value pair, supports multiple of the same key per user
     *
     * @param string $key
     * @param string|null $value
     * @return bool
     * @throws \Packages\MetaData\Exceptions\ModelNotSavedException
     */
    public function addMeta(string $key, ?string $value): bool
    {
        $this->checkIfModelExists();

        return (bool) $this->meta()->create([
            'key'   => $key,
            'value' => $value,
        ]);
    }

    ////////////////////////////////////
    //// Query Scopes
    ////////////////////////////////////

    /**
     * Adds the whereMeta scope to the query builder
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|callable $key   If a string, the meta key to search. If callable, it will be passed as the whereHas query callable.
     * @param mixed|null $delimiter
     * @param mixed|null $value
     *
     * @return void
     */
    public function scopeWhereMeta($query, $key, $delimiter=null, $value=null)
    {
        if (is_callable($key))
        {
            $query->whereHas('meta', $key);
            return;
        }

        $query->whereHas('meta', function($query) use ($key, $delimiter, $value) {
            $query->where('key', $key)
                  ->where('value', $delimiter, $value);
        });
    }

    /**
     * Adds a shortcut for eager loading meta data via withMeta()
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return void
     */
    public function scopeWithMeta($query)
    {
        $query->with(['meta']);
    }

    /**
     * Check if the model has been persisted to the database.
     *
     * @throws \Packages\MetaData\Exceptions\ModelNotSavedException
     */
    protected function checkIfModelExists()
    {
        if (! $this->exists) {
            throw new ModelNotSavedException('The ' . get_called_class() . ' model was not saved before meta data was added.');
        }
    }
}
