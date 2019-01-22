<?php
namespace App\Billing\Generators;

use App\Billing\ClientInvoice;
use App\Billing\Contracts\InvoiceableInterface;
use App\Billing\Exceptions\InvalidClientPayers;
use App\Billing\Exceptions\PayerAllowanceExceeded;
use App\Billing\InvoiceItem;
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

    public function __construct(ClientPayerValidator $payerValidator)
    {
        $this->payerValidator = $payerValidator;
    }

    /**
     * @param \App\Client $client
     * @return array
     * @throws \App\Billing\Exceptions\InvalidClientPayers
     * @throws \App\Billing\Exceptions\PayerAllowanceExceeded
     */
    public function generateAll(Client $client): array
    {
        if (!$this->validatePayers($client)) {
            throw new InvalidClientPayers("Client has invalid payers structure.", $client->id);
        }

        $invoiceables = $this->sortInvoiceables(
            $this->getInvoiceables($client)
        );

        DB::beginTransaction();

        foreach($invoiceables as $invoiceable) {
            $this->assignInvoiceable($client, $invoiceable);
        }

        DB::commit();

        return array_values($this->invoices);
    }

    /**
     * @param \App\Client $client
     * @param \App\Billing\ClientPayer $clientPayer
     * @return \App\Billing\ClientInvoice
     */
    public function getInvoice(Client $client, ClientPayer $clientPayer): ClientInvoice
    {
        $payerId = $clientPayer->payer_id;

        if (!isset($this->invoices[$payerId])) {
            $this->invoices[$payerId] = ClientInvoice::create([
                'name' => $this->getInvoiceName($client),
                'client_id' => $clientPayer->client_id,
                'payer_id' => $payerId,
            ]);
        }

        return $this->invoices[$payerId];
    }

    /**
     * @param \App\Client $client
     * @return \App\Billing\Contracts\InvoiceableInterface[]
     */
    public function getInvoiceables(Client $client): array
    {
        $invoiceables = [];
        foreach($this->getInvoiceableClasses() as $class) {
            $invoiceables = array_merge($invoiceables, $class->getItemsForPayment($client)->all());
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
             // Sort by specific payer first, then by date
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
     * @param float $split
     * @param float $allowance
     * @return array
     */
    public function getItemData(InvoiceableInterface $invoiceable, $split = 1.0, $allowance = 999999.99): array
    {
        $total = round(bcmul($invoiceable->getItemUnits(), $invoiceable->getClientRate(), 4), 2);
        $amountDue = round(bcmul($invoiceable->getAmountDue(), $split, 4), 2);

        if ($amountDue > $allowance) {
            $amountDue = $allowance;
        }

        return [
            'name' => $invoiceable->getItemName(),
            'group' => $invoiceable->getItemGroup(),
            'units' => $invoiceable->getItemUnits(),
            'rate' => $invoiceable->getClientRate(),
            'date' => $invoiceable->getItemDate(),
            'total' => $total,
            'amount_due' => $amountDue,
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
        return ClientInvoice::where('client_id', $client->id)->max("name") ?? "1";
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
            // Get invoiceable item data
            $allowance = $this->getPayerAllowance($clientPayer, $invoiceable->getItemDate());
            $split = $clientPayer->getSplitPercentage();
            $data = $this->getItemData($invoiceable, $split, $allowance);
            // Make item and associate invoiceable
            $item = new InvoiceItem($data);
            $item->associateInvoiceable($invoiceable);
            // Add item to invoice
            $invoice = $this->getInvoice($client, $clientPayer);
            $invoice->addItem($item);
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
            throw new InvalidClientPayers('Unable to assign invoiceable due to an invalid client structure for client ' . $client->id . '.');
        }
    }
}