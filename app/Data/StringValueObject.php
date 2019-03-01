<?php
namespace App\Data;

use JsonSerializable;
use ReflectionClass;

abstract class StringValueObject implements JsonSerializable
{
    /** @var string */
    private $value;

    protected function setValue(string $value)
    {
        $this->value = $value;
    }

    public function value(): string
    {
        return $this->value;
    }


    public function equals(StringValueObject $object)
    {
        return $object->value() === $this->value();
    }

    protected function assertValueInConstants(string $value)
    {
        $constants = (new ReflectionClass($this))->getConstants();
        if (!in_array($value, $constants)) {
            throw new \InvalidArgumentException("The provided value is not an available option.");
        }
    }

    public function __toString()
    {
        return $this->value();
    }

    public function jsonSerialize()
    {
        return $this->value();
    }
}