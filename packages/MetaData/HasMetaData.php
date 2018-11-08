<?php
namespace Packages\MetaData;

use Packages\MetaData\Exceptions\ModelNotSavedException;

/**
 * Trait HasMetaData
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
     * @return mixed
     */
    public function getMeta($keys = null, $single = false)
    {
        $query = $this->meta()
                      ->select(['key', 'value']);

        if ($keys) {
            $query->whereIn('key', (array) $keys);
        }

        if (is_string($keys) && $single) {
            $value = $query->value('value');
            return $this->decodeValue($value);
        }

        return $query->get()->map(function ($meta) {
            $meta->value = $this->decodeValue($meta->value);
            return $meta;
        });
    }

    /**
     * Returns the first value found from a key
     *
     * @param string $key
     * @return mixed
     */
    public function getSingleMeta(string $key)
    {
        return $this->getMeta($key, true);
    }

    /**
     * Updates, or creates, a single meta key
     *
     * @param string $key
     * @param mixed $value
     * @return bool
     * @throws \Packages\MetaData\Exceptions\ModelNotSavedException
     */
    public function setMeta(string $key, $value)
    {
        $value = $this->encodeValue($value);

        if ($existing = $this->meta()->where('key', $key)->first()) {
            return $existing->update(['value' => $value]);
        }

        return $this->addMeta($key, $value);
    }

    /**
     * Adds a new key=>value pair, supports multiple of the same key per user
     *
     * @param string $key
     * @param mixed $value
     * @return bool
     * @throws \Packages\MetaData\Exceptions\ModelNotSavedException
     */
    public function addMeta(string $key, $value)
    {
        $this->checkIfModelExists();
        $value = $this->encodeValue($value);

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
     * @return mixed
     */
    public function scopeWhereMeta($query, $key, $delimiter=null, $value=null)
    {
        if (is_callable($key))
        {
            return $query->whereHas('meta', $key);

        }
        return $query->whereHas('meta', function($query) use ($key, $delimiter, $value) {
            $query->where('key', $key)
                  ->where('value', $delimiter, $value);
        });
    }

    /**
     * Adds a shortcut for eager loading meta data via withMeta()
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return mixed
     */
    public function scopeWithMeta($query)
    {
        return $query->with(['meta']);
    }

    ////////////////////////////////////
    //// Private Methods
    ////////////////////////////////////

    /**
     * Serialize non-scalar values for storage in the database
     *
     * @param $value
     * @return string
     */
    protected function encodeValue($value)
    {
        if (!is_scalar($value)) {
            $value = $this->prefixEncodedValue(serialize($value));
        }
        return $value;
    }

    /**
     * Decode a serialized value
     *
     * @param $value
     * @return mixed
     */
    protected function decodeValue($value)
    {
        if ($this->isEncoded($value)) {
            $prefix = $this->prefixEncodedValue();
            $value = substr($value, strlen($prefix));
            return unserialize($value);
        }
        return $value;
    }

    /**
     * Check if a value is encoded
     *
     * @param $value
     * @return bool
     */
    protected function isEncoded($value)
    {
        $prefix = $this->prefixEncodedValue();
        return substr($value, 0, strlen($prefix)) === $prefix;
    }

    /**
     * Prefix a string to signify it is encoded
     *
     * @param string $value
     * @return string
     */
    protected function prefixEncodedValue($value = '') {
        return '__SERIALIZED_' . $value;
    }

    /**
     * Check if the model has been persisted to the database.
     *
     * @throws \Packages\MetaData\Exceptions\ModelNotSavedException
     */
    protected function checkIfModelExists()
    {
        if (! $this->exists) {
            throw new ModelNotSavedException('The ' . get_called_class() . ' model was not saved before metadata was added.');
        }
    }
}
