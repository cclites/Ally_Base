<?php
namespace App\Billing\Generators;

use App\Billing\ClientInvoice;
use App\Billing\ClientInvoiceItem;
use App\Billing\Contracts\InvoiceableInterface;
use App\Billing\Events\InvoiceableInvoiced;
use App\Billing\Exceptions\InvalidClientPayers;
use App\Billing\Exceptions\PayerAllowanceExceeded;
use App\Billing\BaseInvoiceItem;
use App\Billing\Payer;
use App\Billing\Validators\ClientPayerValidator;
use App\Client;
use App\Billing\ClientPayer;
use Carbon\Carbon;
use DB;

class ClientInvoiceGenerator extends BaseInvoiceGenerator
{
    /**
     * @var \App\Billing\Validators\ClientPayerValidator
     */
    protected $payerValidator;

    /**
     * A map of payer ID => ClientInvoice
     *
     * @var ClientInvoice[]
     */
    protected $invoices = [];

    /**
     * A hash table of split payer amounts used by getAmountDue()
     *
     * @var array
     */
    protected $splitPayerAmounts = [];

    public function __construct(ClientPayerValidator $payerValidator)
    {
        $this->payerValidator = $payerValidator;
    }

    /**
     * @param \App\Client $client
     * @param \Carbon\Carbon $endDateUtc
     * @return array
     * @throws \App\Billing\Exceptions\InvalidClientPayers
     * @throws \App\Billing\Exceptions\PayerAllowanceExceeded
     */
    public function generateAll(Client $client, Carbon $endDateUtc = null): array
    {
        if (!$endDateUtc) {
            $endDateUtc = Carbon::now();
        }

        if (!$this->validatePayers($client)) {
            throw new InvalidClientPayers("Client has invalid payers structure.", $client->id);
        }

        $invoiceables = $this->sortInvoiceables(
            $this->getInvoiceables($client, $endDateUtc)
        );

        $this->clearExistingInvoices();

        if (count($invoiceables)) {
            DB::beginTransaction();

            try {
                foreach($invoiceables as $invoiceable) {
                    $this->assignInvoiceable($client, $invoiceable);
                }
            }
            catch (\Throwable $e) {
                DB::rollBack();
                $this->clearExistingInvoices();
                throw $e;
            }

            DB::commit();
        }

        foreach($invoiceables as $invoiceable) {
            event(new InvoiceableInvoiced($invoiceable));
        }

        return array_values($this->invoices);
    }

    /**
     *  Clear invoices from previous state
     */
    public function clearExistingInvoices()
    {
        $this->invoices = [];
    }

    /**
     * @param \App\Client $client
     * @param \App\Billing\ClientPayer $clientPayer
     * @return \App\Billing\ClientInvoice
     */
    public function getInvoice(Client $client, ClientPayer $clientPayer): ClientInvoice
    {
        $clientPayerId = $clientPayer->id;

        if (!isset($this->invoices[$clientPayerId])) {
            $this->invoices[$clientPayerId] = ClientInvoice::create([
                'name' => $this->getInvoiceName($client),
                'client_id' => $clientPayer->client_id,
                'client_payer_id' => $clientPayerId,
                'offline' => $clientPayer->isOffline(),
            ]);
        }

        return $this->invoices[$clientPayerId];
    }

    /**
     * @param \App\Client $client
     * @param \Carbon\Carbon $endDateUtc
     * @return \App\Billing\Contracts\InvoiceableInterface[]
     */
    public function getInvoiceables(Client $client, Carbon $endDateUtc): array
    {
        $invoiceables = [];
        foreach($this->getInvoiceableClasses() as $class) {
            $invoiceables = array_merge($invoiceables, $class->getItemsForPayment($client, $endDateUtc)->all());
        }

        return $invoiceables;
    }

    /**
     * @param InvoiceableInterface[] $invoiceables
     * @return InvoiceableInterface[]
     */
    public function sortInvoiceables(array $invoiceables): array
    {
        usort($invoiceables, function(InvoiceableInterface $invoiceableA, InvoiceableInterface $invoiceableB) {
             // Sort credit adjustments first, then specific payers, then by date
            if (($invoiceableA->getClientRate() >= 0) !== ($invoiceableB->getClientRate() >= 0)) {
                return $invoiceableA->getClientRate() >= 0 ? 1 : -1;
            }

            if ($invoiceableA->getPayerId() === $invoiceableB->getPayerId()) {
                return strtotime($invoiceableA->getItemDate()) - strtotime($invoiceableB->getItemDate());
            }

            return ($invoiceableA->getPayerId() - $invoiceableB->getPayerId()) * -1;
        });

        return $invoiceables;
    }

    /**
     * @param ClientPayer[] $payers
     * @return ClientPayer[]
     */
    public function sortPayers(array $payers): array
    {
        usort($payers, function(ClientPayer $payerA, ClientPayer $payerB) {
            if (
                $payerA->payment_allocation === $payerB->payment_allocation
                || $payerA->isAllowanceType() && $payerB->isAllowanceType()
            ) {
                return $payerA->priority - $payerB->priority;
            }

            if ($payerA->isAllowanceType()) {
                return -1;
            }
            if ($payerB->isAllowanceType()) {
                return 1;
            }
            if ($payerA->isBalanceType()) {
                return 1;
            }

            return 0;
        });

        return $payers;
    }


    /**
     * @param \App\Billing\Contracts\InvoiceableInterface $invoiceable
     * @param float $clientRate
     * @param float $amountDue
     * @param bool $wasSplit
     * @return array
     */
    public function getItemData(InvoiceableInterface $invoiceable, float $clientRate, float $amountDue, bool $wasSplit = false): array
    {
        return [
            'name' => $invoiceable->getItemName(ClientInvoice::class),
            'group' => $invoiceable->getItemGroup(ClientInvoice::class),
            'units' => $invoiceable->getItemUnits(),
            'rate' => $clientRate,
            'date' => $invoiceable->getItemDate(),
            'total' => round(bcmul($invoiceable->getItemUnits(), $clientRate, 4), 2),
            'amount_due' => $amountDue,
            'notes' => $invoiceable->getItemNotes(),
            'was_split' => $wasSplit,
        ];
    }

    /**
     * @param \App\Client $client
     * @return bool
     */
    public function validatePayers(Client $client): bool
    {
        return $this->payerValidator->validate($client);
    }

    /**
     * @param \App\Client $client
     * @return mixed|string
     */
    public function getInvoiceName(Client $client)
    {
        return ClientInvoice::getNextName($client->id);
    }

    /**
     * @param \App\Billing\ClientPayer $clientPayer
     * @param string $date
     * @return float
     */
    public function getPayerAllowance(ClientPayer $clientPayer, string $date): float
    {
        return $clientPayer->getAllowance($date);
    }

    /**
     * @param \App\Client $client
     * @param \App\Billing\Contracts\InvoiceableInterface $invoiceable
     * @throws \App\Billing\Exceptions\InvalidClientPayers
     * @throws \App\Billing\Exceptions\PayerAllowanceExceeded
     */
    protected function assignInvoiceable(Client $client, InvoiceableInterface $invoiceable): void
    {
        $payers = $this->sortPayers(
            $client->getPayers($invoiceable->getItemDate())->all()
        );

        foreach($payers as $clientPayer) {
            if ($invoiceable->getAmountDue() === 0) {
                return;
            }
            if ($invoiceable->getPayerId() !== null && $invoiceable->getPayerId() !== $clientPayer->payer_id) {
                continue;
            }
            if ($invoiceable->getPayerId() === null && $clientPayer->isManualType()) {
                continue;
            }
            // Get invoiceable item data
            $allowance = $this->getPayerAllowance($clientPayer, $invoiceable->getItemDate());
            $clientRate = $this->getClientRate($clientPayer, $invoiceable);
            [$amountDue, $allyFee, $wasSplit] = $this->getAmountDueAndFee($clientPayer, $invoiceable, $allowance);
            $data = $this->getItemData($invoiceable, $clientRate, $amountDue, $wasSplit);
            // Make item and associate invoiceable
            $item = new ClientInvoiceItem($data);
            $item->associateInvoiceable($invoiceable);
            // Add item to invoice
            $invoice = $this->getInvoice($client, $clientPayer);
            $invoice->addItem($item);
            $invoiceable->addAmountInvoiced($item, $amountDue, $allyFee);
            // Reduce allowance by the amount due of the item
            $allowance -= $data['amount_due'];

            if ($allowance <= 0.0
                && $invoiceable->getPayerId() === $clientPayer->payer_id
                && $invoiceable->getAmountDue() > 0.0)
            {
                throw new PayerAllowanceExceeded("Invoiceable item is assigned to payer " . $clientPayer->payer->name . " but payer allowance has been exceeded.");
            }
        }

        if ($invoiceable->getAmountDue() > 0.0) {
            if ($invoiceable->getPayerId()) {
                $identifier = get_class($invoiceable) . ':' . $invoiceable->id ?? '';
                $assignedPayer = Payer::find($invoiceable->getPayerId());
                if (!$assignedPayer) {
                    throw new InvalidClientPayers("Invoiceable $identifier was added to an unknown payer.");
                }
                $payerName = $assignedPayer->name();
                throw new InvalidClientPayers("Invoiceable $identifier was added to $payerName but this payer is not available for this client.");
            }

            throw new InvalidClientPayers('Unable to assign invoiceable due to an invalid client structure for client ' . $client->id . '.');
        }
    }

    protected function getClientRate(ClientPayer $clientPayer, InvoiceableInterface $invoiceable): float
    {
        $clientRate = $invoiceable->getClientRate();
        return add($clientRate, $this->getAllyFee($clientRate, $invoiceable, $clientPayer, 4), 4);
    }

    /**
     * @param ClientPayer $clientPayer
     * @param InvoiceableInterface $invoiceable
     * @param float $allowance
     * @return float[]
     */
    protected function getAmountDueAndFee(ClientPayer $clientPayer, InvoiceableInterface $invoiceable, $allowance = 999999.99): array
    {
        $amountDue = $invoiceable->getAmountDue();
        $split = $clientPayer->getSplitPercentage();
        $wasSplit = false;

        if ($split < 1.0) {
            // Store amount owed by split payers (see allowance_payer_before_a_split_payer_does_not_skew_the_amounts)
            if (!isset($this->splitPayerAmounts[$invoiceable->getItemHash()])) {
                $this->splitPayerAmounts[$invoiceable->getItemHash()] = $amountDue;
            }
            $splitAmount = $this->splitPayerAmounts[$invoiceable->getItemHash()];
            $amountDue = multiply($splitAmount, $split);
            $wasSplit = true;
        }

        $allyFee = $this->getAllyFee($amountDue, $invoiceable, $clientPayer, 2);

        if (($amountDue + $allyFee) > $allowance) {
            $amountDue = divide(
                $allowance, divide(
                    add($amountDue, $allyFee),
                    $amountDue
                )
            );
            $allyFee = subtract($allowance, $amountDue);
            $wasSplit = true;
        }

        return [
            add($amountDue, $allyFee),
            $allyFee,
            $wasSplit
        ];
    }

    protected function getAllyFee(float $amount, InvoiceableInterface $invoiceable, ClientPayer $clientPayer, $decimalPrecision = 4): float
    {
        if ($invoiceable->hasFeeIncluded()) {
            return 0.0;
        }

        $pct = $clientPayer->getAllyPercentage();

        return multiply($amount, $pct, $decimalPrecision);
    }
}