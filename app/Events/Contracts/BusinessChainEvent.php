<?php
namespace App\Events\Contracts;

use App\Business;
use App\BusinessChain;

interface BusinessChainEvent
{
    public function __construct(BusinessChain $businessChain);
    public function getBusinessChain(): BusinessChain;
}