<?php

namespace App;

use MyCLabs\Enum\Enum;

/**
 * QuickbooksInvoiceStatus Enum
 *
 * @method static QuickbooksInvoiceStatus READY()
 * @method static QuickbooksInvoiceStatus QUEUED()
 * @method static QuickbooksInvoiceStatus PROCESSING()
 * @method static QuickbooksInvoiceStatus TRANSFERRED()
 * @method static QuickbooksInvoiceStatus ERRORED()
 */
class QuickbooksInvoiceStatus extends Enum
{
    private const READY = 'ready';
    private const QUEUED = 'queued';
    private const PROCESSING = 'processing';
    private const TRANSFERRED = 'transferred';
    private const ERRORED = 'errored';
}
