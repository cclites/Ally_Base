<?php

namespace App;

class ClientType 
{
    const PRIVATE_PAY = 'private_pay';
    const MEDICAID = 'medicaid';
    const LTCI = 'LTCI';
    const VA = 'VA';
    const LEAD = 'lead_agency';

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
