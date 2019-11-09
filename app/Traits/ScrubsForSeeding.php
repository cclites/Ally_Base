<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait ScrubsForSeeding
{
    /**
     * Get an array of scrubbed data to replace the original.
     *
     * @param \Faker\Generator $faker
     * @param bool $fast
     * @return array
     */
    abstract public static function getScrubbedData(\Faker\Generator $faker, bool $fast) : array;

    /**
     * Get the query used to identify records that will be scrubbed.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function getScrubQuery() : Builder
    {
        if (in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses(static::class))) {
            return static::withTrashed();
        }

        return static::query();
    }
}