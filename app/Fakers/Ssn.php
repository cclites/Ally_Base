<?php

namespace App\Fakers;

class Ssn extends \Faker\Provider\Base
{
    /**
     * Generate a social security number.
     *
     * @return string
     */
    public function ssn() : string
    {
        return (string) (mt_rand(100, 999) . '-' . mt_rand(10, 99) . '-' . mt_rand(1000, 9999));
    }
}