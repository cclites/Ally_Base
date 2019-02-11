<?php
namespace App\Contracts;

interface DepositAggregatorInterface
{
    /**
     * @return \App\Billing\Deposit
     */
    public function getDeposit();

    /**
     * @return \App\Shift[]
     */
    public function getShifts();

    /**
     * @return array
     */
    public function getShiftIds();

    /**
     * @return \App\Billing\GatewayTransaction|false
     */
    public function deposit();


}