<?php
namespace App\Billing\Contracts;

interface InvoiceInterface
{
    function getName(): string;
    function getDate(): string;
    function getAmount(): float;
    function getAmountPaid(): float;
    function getAmountDue(): float;
}