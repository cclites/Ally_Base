<?php
namespace App\Billing\Contracts;

use App\Business;
use App\Caregiver;
use App\Client;
use Illuminate\Support\Collection;

interface Invoiceable
{
    /**
     * @param \App\Client $client
     * @return \Illuminate\Support\Collection|\App\Billing\Contracts\Invoiceable[]
     */
    public static function getItemsForPayment(Client $client): Collection;

    /**
     * @param \App\Caregiver $caregiver
     * @return \Illuminate\Support\Collection|\App\Billing\Contracts\Invoiceable[]
     */
    public static function getItemsForCaregiverDeposit(Caregiver $caregiver): Collection;

    /**
     * @param \App\Business $business
     * @return \Illuminate\Support\Collection|\App\Billing\Contracts\Invoiceable[]
     */
    public static function getItemsForProviderDeposit(Business $business): Collection;

    public function getUnits(): float;

    public function getItemName(): string;

    public function getItemGroup(): ?string;

    public function getPaymentRate(): float;

    public function getCaregiverRate(): float;

    public function getProviderRate(): float; // Should we use getAllyRate() instead since provider rate will be calculated?
}