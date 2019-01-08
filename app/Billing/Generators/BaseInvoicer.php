<?php
namespace App\Billing\Generators;

use App\Billing\Invoiceable\ShiftService;

abstract class BaseInvoicer
{
    /**
     * Array of invoiceable model classes  (database mapping string => model class)
     * @var array
     */
    public static $invoiceables = [
        'shift_service' => ShiftService::class,
    ];
}