<?php

namespace App\Claims;

use App\BaseEnum;

/**
 * ClaimInvoiceType Enum
 *
 * @method static ClaimInvoiceType SINGLE()
 * @method static ClaimInvoiceType CLIENT()
 * @method static ClaimInvoiceType PAYER()
 */
class ClaimInvoiceType extends BaseEnum
{
    private const SINGLE = 'single';
    private const CLIENT = 'client';
    private const PAYER = 'payer';
}
