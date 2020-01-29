<?php
namespace App\Billing\Validators;

use App\Client;
use App\Billing\ClientPayer;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ClientPayerValidator
{
    /**
     * @var string|null
     */
    protected $error;

    public function validate(Client $client): bool
    {
        $payers = $client->payers;

        // Validate client has a payer entity
        if (!$payers->count()) {
            return $this->error("Client has no payers assigned.");
        }

        // Validate client payers all have valid types
        if (!$this->validatePayerTypes($payers)) {
            return false;
        }

        // Build dates array based on the ranges
        $dates = $this->buildDatesArray($payers);

        // Validate against each date
        foreach($dates as $date) {
            if (!$this->validateByDate($client, $date)) return false;
        }

        return true;
    }

    /**
     * @param \App\Client $client
     * @param string $date
     * @return bool
     */
    public function validateByDate(Client $client, string $date = 'now'): bool
    {
        $payers = $client->getPayers($date);

        if (!$payers->count()) {
            return $this->error("Client has no payers assigned on $date.");
        }

        if ($this->countOffline($payers) && $this->countOffline($payers) !== $payers->count()) {
            return $this->error("You cannot mix offline and online payers on $date.");
        }

        if ($duplicatePayer = $this->findDuplicate($payers)) {
            $name = $duplicatePayer->name();
            return $this->error("There is a duplicate payer assignment for $name on $date.");
        }

        $balanceCount = $this->countType($payers, "balance", $date);
        if ($balanceCount > 1) {
            return $this->error("A client can only have one balance payer, client has $balanceCount balance payers on $date.");
        }

        // If split, validate balance and <=1  or equal to 1
        if ($this->findType($payers, "split")) {
            return $this->validateSplitPayers($payers, $balanceCount === 1);
        }

        // Else, require one balance
        if ($balanceCount < 1) {
            return $this->error("A balance payer is required for all dates, balance payer missing on $date.");
        }

        return true;
    }

    /**
     * @return string|null
     */
    public function getErrorMessage(): string {
        return $this->error ?: '';
    }

    /**
     *
     * @param \Illuminate\Support\Collection|\App\Billing\ClientPayer[] $payers
     * @return \App\Billing\ClientPayer|null
     */
    function findDuplicate(Collection $payers): ?ClientPayer
    {
        $hashTable = [];
        foreach($payers as $payer)
        {
            $hash = $payer->payer_id;
            if (isset($hashTable[$hash])) {
                return $payer;
            }
            $hashTable[$hash] = 1;
        }

        return null;
    }

    /**
     *
     * @param \Illuminate\Support\Collection|\App\Billing\ClientPayer[] $payers
     * @return bool
     */
    function validatePayerTypes(Collection $payers): bool
    {
        foreach($payers as $payer) {
            if (!in_array($payer->payment_allocation, ClientPayer::$allocationTypes)) {
                return $this->error($payer->payment_allocation . " is not a valid payment allocation type.");
            }
        }
        return true;
    }

    function validateSplitPayers(Collection $payers, bool $hasBalancePayer): bool
    {
        // Validate priorities (split should never precede anything but balance/another split)
        $lastNonBalance = -1;
        $nonBalanceType = "";
        $firstSplit = 999;
        foreach($payers as $payer) {
            if ($payer->payment_allocation === "split" && $payer->priority < $firstSplit) {
                $firstSplit = $payer->priority;
            } else if (!in_array($payer->payment_allocation, ["balance", "split"]) && $payer->priority > $lastNonBalance) {
                $lastNonBalance = $payer->priority;
                $nonBalanceType = $payer->payment_allocation;
            }
        }
        if ($firstSplit < $lastNonBalance) {
            return $this->error("A split payer cannot have a higher priority than a " . $nonBalanceType);
        }

        // Count total payer percentage
        $percentage = $payers->reduce(function($carry, $payer) {
            return $carry + $payer->split_percentage;
        }, 0);

        if ($percentage > 1.0) {
            return $this->error("The split payer percentages cannot add up to more than 100%.");
        }

        if ($hasBalancePayer) {
            return true;
        }
        if ($percentage < 1.0) {
            return $this->error("The split payer percentages must add up to 100%.");
        }

        return true;
    }

    /**
     * @param \Illuminate\Support\Collection|\App\Billing\ClientPayer[] $payers
     * @param string $type
     * @return null|\App\Billing\ClientPayer
     */
    protected function findType(Collection $payers, string $type)
    {
        return $payers->first(function(ClientPayer $payer) use ($type) {
            return $payer->payment_allocation === $type;
        });
    }

    /**
     * @param \Illuminate\Support\Collection|\App\Billing\ClientPayer[] $payers
     * @param string $type
     * @return int
     */
    protected function countType(Collection $payers, string $type): int
    {
        return $payers->filter(function(ClientPayer $payer) use ($type) {
            return $payer->payment_allocation === $type;
        })->count();
    }

    /**
     * @param \Illuminate\Support\Collection|\App\Billing\ClientPayer[] $payers
     * @return int
     */
    protected function countOffline(Collection $payers): int
    {
        return $payers->filter(function(ClientPayer $payer) {
            return $payer->isOffline();
        })->count();
    }

    /**
     * @param string $message
     * @return false
     */
    protected function error(string $message): bool
    {
        $this->error = $message;
        return false;
    }

    /**
     * @param $payers
     * @return array
     */
    protected function buildDatesArray($payers): array
    {
        $checkUntilYear = date('Y') + 5;
        $dates = [Carbon::now()->toDateString()]; // handles gaps in front
        foreach ($payers as $payer) {
            $dates[] = $payer->effective_start;
            $dates[] = $payer->effective_end;

            $end = Carbon::parse($payer->effective_end);
            if ($end->year < $checkUntilYear) {
                $dates[] = $end->addDay()->toDateString(); // handles gaps between and at the end
            }
        }

        return $dates;
    }
}