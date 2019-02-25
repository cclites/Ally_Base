<?php
namespace App\Data\Traits;

use ReflectionClass;
use ReflectionProperty;

trait ReflectsToArray
{
    function toArray()
    {
        $properties = (new ReflectionClass($this))->getProperties();
        return array_reduce($properties, function(array $carry, ReflectionProperty $property) {
            $carry[$property->name] = $this->{$property->name};
            return $carry;
        }, []);
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }
}