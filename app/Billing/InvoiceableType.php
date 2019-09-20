<?php
namespace App\Billing;

use MyCLabs\Enum\Enum;

/**
 * InvoiceableType Enum
 *
 * @method static ClaimStatus SHIFT()
 * @method static ClaimStatus SHIFT_SERVICE()
 * @method static ClaimStatus SHIFT_ADJUSTMENT()
 * @method static ClaimStatus SHIFT_EXPENSE()
 */
class InvoiceableType extends Enum
{
    private const SHIFT = 'shifts';
    private const SHIFT_SERVICE = 'shift_services';
    private const SHIFT_ADJUSTMENT = 'shift_adjustments';
    private const SHIFT_EXPENSE = 'shift_expenses';
}
