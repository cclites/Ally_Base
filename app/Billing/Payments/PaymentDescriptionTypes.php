<?php


namespace App\Billing\Payments;

use MyCLabs\Enum\Enum;


/**
 * Class PaymentDescriptionTypes
 *
 * @method static PAYMENT_APPLIED()
 * @method static PARTIAL_PAYMENT_APPLIED()
 * @method static OVERPAYMENT()
 * @method static WRITE_OFF()
 * @method static DENIAL()
 * @method static SUPPLIER_CONTRIBUTION()
 * @method static INTEREST()
 * @method static DISCOUNT()
 */
class PaymentDescriptionTypes extends enum
{
    private const PAYMENT_APPLIED = 'payment_applied';
    private const PARTIAL_PAYMENT_APPLIED = 'partial_payment_applied';
    private const OVERPAYMENT = 'overpayment';
    private const WRITE_OFF = 'write_off';
    private const DENIAL = 'denial';
    private const SUPPLIER_CONTRIBUTION = 'supplier_contribution';
    private const INTEREST = 'interest';
    private const DISCOUNT = 'discount';

}