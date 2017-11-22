<?php
namespace App\Contracts;

interface DepositAggregatorInterface
{
    /**
     * @return \App\Deposit
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
     * @return \App\GatewayTransaction|false
     */
    public function deposit();


}