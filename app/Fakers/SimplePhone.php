<?php

namespace App\Fakers;

class SimplePhone extends \Faker\Provider\Base
{
    /**
     * Generate a simple 10 digit phone number.
     *
     * @return string
     */
    public function simple_phone() : string
    {
        return (string) mt_rand(3000000000, 9999999999);
    }
}