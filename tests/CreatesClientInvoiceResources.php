<?php

namespace Tests;

use App\Billing\ClientPayer;
use App\Billing\Contracts\InvoiceableInterface;
use App\Billing\Invoiceable\ShiftAdjustment;
use App\Billing\Invoiceable\ShiftService;
use App\Billing\Payer;
use App\Billing\Payments\Methods\CreditCard;
use App\Shift;

/**
 * Trait CreatesClientInvoiceResources
 * Note: This trait requires a $client property to be defined
 *
 * @package Tests
 */
trait CreatesClientInvoiceResources
{
    private function createAllowancePayer(float $allowance, string $effective_start = '2019-01-01', string $effective_end = '9999-12-31', string $allocation_type = ClientPayer::ALLOCATION_MONTHLY): ClientPayer
    {
        $payer = factory(Payer::class)->create();
        $clientPayer = new ClientPayer([
            'payer_id' => $payer->id,
            'effective_start' => $effective_start,
            'effective_end' => $effective_end,
            'payment_allocation' => $allocation_type,
            'payment_allowance' => $allowance
        ]);

        $this->client->payers()->save($clientPayer);
        return $clientPayer;
    }

    private function createSplitPayer(float $splitPercentage, string $effective_start = '2019-01-01', string $effective_end = '9999-12-31', $payerId = null): ClientPayer
    {
        if ($payerId === null) {
            $payer = factory(Payer::class)->create();
            $payerId = $payer->id;
        }
        $clientPayer = new ClientPayer([
            'payer_id' => $payerId,
            'effective_start' => $effective_start,
            'effective_end' => $effective_end,
            'payment_allocation' => ClientPayer::ALLOCATION_SPLIT,
            'split_percentage' => $splitPercentage
        ]);

        $this->client->payers()->save($clientPayer);
        return $clientPayer;
    }

    private function createBalancePayer(string $effective_start = '2019-01-01', string $effective_end = '9999-12-31', $payerId = null): ClientPayer
    {
        if ($payerId === null) {
            $payer = factory(Payer::class)->create();
            $payerId = $payer->id;
        }
        $clientPayer = new ClientPayer([
            'payer_id' => $payerId,
            'effective_start' => $effective_start,
            'effective_end' => $effective_end,
            'payment_allocation' => ClientPayer::ALLOCATION_BALANCE,
        ]);

        $this->client->payers()->save($clientPayer);
        return $clientPayer;
    }

    private function createPrivateBalancePayer(string $effective_start = '2019-01-01', string $effective_end = '9999-12-31'): ClientPayer
    {
        return $this->createBalancePayer($effective_start, $effective_end, Payer::PRIVATE_PAY_ID);
    }


    private function createPrivateSplitPayer(float $splitPercentage, string $effective_start = '2019-01-01', string $effective_end = '9999-12-31'): ClientPayer
    {
        return $this->createSplitPayer($splitPercentage, $effective_start, $effective_end, Payer::PRIVATE_PAY_ID);
    }


    private function createService(float $amount, string $date = '2019-01-15', ?int $payerId = null): InvoiceableInterface
    {
        $shift = factory(Shift::class)->create([
            'client_id' => $this->client->id,
            'payer_id' => null,
            'service_id' => null,
            'checked_in_time' => $date . ' 12:00:00',
            'status' => Shift::WAITING_FOR_INVOICE,
        ]);

        $shiftService = factory(ShiftService::class)->create([
            'shift_id' => $shift->id,
            'payer_id' => $payerId,
            'duration' => 1,
            'client_rate' => $amount,
            'caregiver_rate' => round($amount * .75, 2),
            'ally_rate' => null,
        ]);

        return $shiftService;
    }

    private function createServiceHours(float $rate, float $duration, string $date = '2019-01-15', ?int $payerId = null): InvoiceableInterface
    {
        $shift = factory(Shift::class)->create([
            'client_id' => $this->client->id,
            'payer_id' => null,
            'service_id' => null,
            'checked_in_time' => $date . ' 12:00:00',
            'status' => Shift::WAITING_FOR_INVOICE,
        ]);

        $shiftService = factory(ShiftService::class)->create([
            'shift_id' => $shift->id,
            'payer_id' => $payerId,
            'duration' => $duration,
            'client_rate' => $rate,
            'caregiver_rate' => round($rate * .75, 2),
            'ally_rate' => null,
        ]);

        return $shiftService;
    }

    private function createShiftWithExpense(float $amount, string $date = '2019-01-15', ?int $payerId = null): InvoiceableInterface
    {
        $shift = factory(Shift::class)->create([
            'client_id' => $this->client->id,
            'payer_id' => $payerId,
            'service_id' => \App\Billing\Service::inRandomOrder()->first() ?? factory(\App\Billing\Service::class)->create(),
            'caregiver_rate' => 0,
            'client_rate' => 0,
            'other_expenses' => $amount,
            'checked_in_time' => $date . ' 12:00:00',
            'status' => Shift::WAITING_FOR_INVOICE,
        ]);

        return $shift;
    }

    private function createShiftWithMileage(float $rate, float $miles, string $date = '2019-01-15', ?int $payerId = null): InvoiceableInterface
    {
        $shift = factory(Shift::class)->create([
            'client_id' => $this->client->id,
            'payer_id' => $payerId,
            'service_id' => \App\Billing\Service::inRandomOrder()->first() ?? factory(\App\Billing\Service::class)->create(),
            'caregiver_rate' => 0,
            'client_rate' => 0,
            'mileage' => $miles,
            'checked_in_time' => $date . ' 12:00:00',
            'status' => Shift::WAITING_FOR_INVOICE,
        ]);
        $shift->business->update(['mileage_rate' => $rate]);

        return $shift;
    }

    private function createCreditAdjustment(float $amount, string $date = '2019-01-15', ?int $payerId = null)
    {
        $adjustment = factory(ShiftAdjustment::class)->create([
            'client_id' => $this->client->id,
            'payer_id' => $payerId,
            'client_rate' => -$amount,
            'units' => 1,
            'status' => 'WAITING_FOR_INVOICE',
            'created_at' => $date . ' 00:00:00',
            'updated_at' => $date . ' 00:00:00',
        ]);

        return $adjustment;
    }

    private function createCreditCard($type = null)
    {
        $number = (strtolower($type) === 'amex')
            ? "340415772202528"
            : "4111111111111111";

        return factory(CreditCard::class)->create(['number' => $number, 'type' => $type]);
    }
}