<?php

namespace App;

class ClientType 
{
    const PRIVATE_PAY = 'private_pay';
    const MEDICADE = 'medicade';
    const LTCI = 'LTCI';
    const VA = 'VA';

    /**
     * Get all of the ClientTypes in array form.
     *
     * @return array
     */
    public static function all()
    {
        $cls = new \ReflectionClass(__CLASS__);
        return $cls->getConstants();
    }
}
