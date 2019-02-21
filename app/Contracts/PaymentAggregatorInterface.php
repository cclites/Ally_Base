<?php

namespace App\Contracts;

interface PaymentAggregatorInterface
{

    /**
     * @return \App\Billing\GatewayTransaction|false
     */
    public function charge();

    /**
     * @return \App\Billing\Payment
     */
    public function getPayment();

    /**
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function getEntity();

    /**
     * @return \App\Shift[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getShifts();
}