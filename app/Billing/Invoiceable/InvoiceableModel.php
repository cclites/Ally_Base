<?php
namespace App\Billing\Invoiceable;

use App\AuditableModel;
use App\Billing\Contracts\Invoiceable;
use Packages\MetaData\HasMetaData;
use Packages\MetaData\HasMetaInterface;

abstract class InvoiceableModel extends AuditableModel implements Invoiceable, HasMetaInterface
{
    use HasMetaData;

    ////////////////////////////////////
    //// Relationship Methods
    ////////////////////////////////////

    public function meta()
    {
        return $this->morphMany(InvoiceableMeta::class, 'metable');
    }
}