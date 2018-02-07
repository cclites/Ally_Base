<?php

namespace App\Contracts;

interface PaymentAggregatorInterface
{

    /**
     * @return \App\GatewayTransaction|false
     */
    public function charge();

    /**
     * @return \App\Payment
     */
    public function getPayment();

    /**
     * @return \App\Shift[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getShifts();
}