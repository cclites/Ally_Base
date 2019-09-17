<?php

namespace App\Claims;

use App\BaseEnum;

/**
 * ClaimAdjustmentType Enum
 *
 * @method static ClaimAdjustmentType DENIAL()
 * @method static ClaimAdjustmentType DISCOUNT()
 * @method static ClaimAdjustmentType INTEREST()
 * @method static ClaimAdjustmentType OVERPAYMENT()
 * @method static ClaimAdjustmentType PARTIAL_PAYMENT()
 * @method static ClaimAdjustmentType PAYMENT()
 * @method static ClaimAdjustmentType SUPPLIER_CONTRIBUTION()
 * @method static ClaimAdjustmentType TAKE_BACK()
 * @method static ClaimAdjustmentType WRITE_OFF()
 */
class ClaimAdjustmentType extends BaseEnum
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
