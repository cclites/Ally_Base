<?php
namespace App\Billing\Generators;

use App\Billing\Invoiceable\ShiftAdjustment;
use App\Billing\Invoiceable\ShiftService;
use App\Shift;

abstract class BaseInvoiceGenerator
{
    /**
     * Array of invoiceable model classes  (database mapping string => model class)
     * @var array
     */
    public static $invoiceables = [
        'shift_adjustment' => ShiftAdjustment::class,
        'shift' => Shift::class,
        'shift_service' => ShiftService::class,
    ];

    /**
     * @return \App\Billing\Contracts\InvoiceableInterface[]
     */
    public function getInvoiceableClasses()
    {
        return array_map(function($class) {
            return is_object($class) ? $class : app($class);
        }, self::$invoiceables);
    }

}