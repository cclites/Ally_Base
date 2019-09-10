<?php

namespace App\Claims;

use App\BaseEnum;

/**
 * ClaimRemitApplicationType Enum
 *
 * @method static ClaimRemitApplicationType DENIAL()
 * @method static ClaimRemitApplicationType DISCOUNT()
 * @method static ClaimRemitApplicationType INTEREST()
 * @method static ClaimRemitApplicationType OVERPAYMENT()
 * @method static ClaimRemitApplicationType PARTIAL_PAYMENT()
 * @method static ClaimRemitApplicationType PAYMENT()
 * @method static ClaimRemitApplicationType SUPPLIER_CONTRIBUTION()
 * @method static ClaimRemitApplicationType TAKE_BACK()
 * @method static ClaimRemitApplicationType WRITE_OFF()
 */
class ClaimRemitApplicationType extends BaseEnum
{
    private const DENIAL = 'denial';
    private const DISCOUNT = 'discount';
    private const INTEREST = 'interest';
    private const OVERPAYMENT = 'overpayment';
    private const PARTIAL_PAYMENT = 'partial';
    private const PAYMENT = 'payment';
    private const SUPPLIER_CONTRIBUTION = 'supplier-contribution';
    private const TAKE_BACK = 'take-back';
    private const WRITE_OFF = 'write-off';
}
