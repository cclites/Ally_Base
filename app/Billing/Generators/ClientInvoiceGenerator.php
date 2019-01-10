<?php
namespace App\Billing\Generators;

use App\Billing\ClientInvoice;
use App\Billing\Contracts\Invoiceable;
use App\Billing\Exceptions\InvalidClientPayers;
use App\Billing\Exceptions\PayerAllowanceExceeded;
use App\Billing\InvoiceItem;
use App\Billing\Validators\ClientPayerValidator;
use App\Client;
use App\Billing\ClientPayer;
use DB;

class ClientInvoiceGenerator extends BaseInvoiceGenerator
{
    /**
     * @var \App\Billing\Validators\ClientPayerValidator
     */
    protected $payerValidator;

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

        $payers = $client->getPayers();
        $invoiceables = $this->getInvoiceables($client);

        DB::beginTransaction();
        $invoices = [];
        $balancePayer = null;
        foreach($payers as $payer) {
            switch($payer->payment_allocation) {
                case 'daily':
                case 'weekly':
                case 'monthly':
                    $invoices[] = $this->generateAllowancePayerInvoice($client, $payer, $invoiceables);
                    break;
                case 'split':
                    $invoices[] = $this->generateSplitPayerInvoice($client, $payer, $invoiceables);
                    break;
                case 'balance':
                    $balancePayer = $payer; // Always process the balance payer last
                    break;
                default:
                    throw new InvalidClientPayers("The payer allocation type " . $payer->payment_allocation . " is not implemented.");
            }
        }
        if ($balancePayer) {
            $this->generateBalancePayerInvoice($client, $balancePayer, $invoiceables);
        }
        DB::commit();

        return $invoices;
    }

    /**
     * @param \App\Client $client
     * @return \App\Billing\Contracts\Invoiceable[]
     */
    public function getInvoiceables(Client $client): array
    {
        $invoiceables = [];
        foreach($this->getInvoiceableClasses() as $class) {
            $invoiceables[] = $class->getItemsForPayment($client);
        }
        return $invoiceables;
    }

    /**
     * @param \App\Billing\Contracts\Invoiceable $invoiceable
     * @param float $split
     * @param float $allowance
     * @return array
     */
    public function getItemData(Invoiceable $invoiceable, $split = 1.0, $allowance = 999999.99): array
    {
        $total = round(bcmul($invoiceable->getItemUnits(), $invoiceable->getClientRate(), 4), 2);
        $amountDue = ($split < 1.0)
            ? round(bcmul($total, $split, 4), 2)
            : $invoiceable->getAmountDue();

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
     * @param \App\Billing\ClientPayer $clientPayer
     * @param Invoiceable[] $invoiceables
     * @param float $split
     * @param float $allowance
     * @return \App\Billing\ClientInvoice
     * @throws PayerAllowanceExceeded
     */
    public function generateInvoice(Client $client, ClientPayer $clientPayer, array $invoiceables, float $split = 1.0, float $allowance = 999999.99): ?ClientInvoice
    {
        // Get invoiceables assigned to this payer or on auto
        $invoiceables = array_merge(
            // Order is important here, we should process items specifically assigned to a payer first (re: PayerAllowanceExceeded)
            $this->filterInvoiceablesByPayers($invoiceables, $clientPayer->payer_id),
            $this->filterInvoiceablesByPayers($invoiceables, null)
        );

        $items = [];
        foreach($invoiceables as $invoiceable) {
            /** @var Invoiceable|\Illuminate\Database\Eloquent\Model $invoiceable */

            // Get invoiceable item data
            $data = $this->getItemData($invoiceable, $split, $allowance);

            // Make item and associate invoiceable
            $item = new InvoiceItem($data);
            $item->invoiceable()->associate($invoiceable);
            $items[] = $item;

            // Reduce allowance by the amount due of the item
            $allowance -= $data['amount_due'];

            // Add amount invoiced to invoiceable item
            $invoiceable->addAmountInvoiced($data['amount_due']);

            if ($allowance <= 0.0
                && $invoiceable->getPayerId() === $clientPayer->payer_id
                && $invoiceable->getAmountDue() > 0.0)
            {
                throw new PayerAllowanceExceeded("Invoiceable item is assigned to payer " . $clientPayer->payer->name . " but payer allowance has been exceeded.");
            }
        }

        if (count($items)) {
            $invoice = ClientInvoice::create([
                'name' => $this->getInvoiceName($client),
                'client_id' => $client->id,
                'payer_id' => $clientPayer->payer_id,
            ]);
            $invoice->items()->saveMany($items);
            return $invoice;
        }

        return null;
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
     * @param \App\Client $client
     * @param \App\Billing\ClientPayer $clientPayer
     * @param array $invoiceables
     * @return \App\Billing\ClientInvoice|null
     * @throws \App\Billing\Exceptions\PayerAllowanceExceeded
     */
    protected function generateSplitPayerInvoice(Client $client, ClientPayer $clientPayer, array $invoiceables): ?ClientInvoice
    {
        $split = $clientPayer->split_percentage;
        return $this->generateInvoice($client, $clientPayer, $invoiceables, $split);
    }

    /**
     * @param \App\Client $client
     * @param \App\Billing\ClientPayer $clientPayer
     * @param array $invoiceables
     * @return \App\Billing\ClientInvoice|null
     * @throws \App\Billing\Exceptions\PayerAllowanceExceeded
     */
    protected function generateAllowancePayerInvoice(Client $client, ClientPayer $clientPayer, array $invoiceables): ?ClientInvoice
    {
        $allowance = $clientPayer->payment_allowance ?? 999999.99;
        return $this->generateInvoice($client, $clientPayer, $invoiceables, 1.0, $allowance);
    }

    /**
     * @param \App\Client $client
     * @param \App\Billing\ClientPayer $clientPayer
     * @param array $invoiceables
     * @return \App\Billing\ClientInvoice|null
     */
    protected function generateBalancePayerInvoice(Client $client, ClientPayer $clientPayer, array $invoiceables): ?ClientInvoice
    {
        return $this->generateInvoice($client, $clientPayer, $invoiceables);
    }

    /**
     * @param Invoiceable[] $invoiceables
     * @param int|null $payerId
     * @return Invoiceable[]
     */
    protected function filterInvoiceablesByPayer(array $invoiceables, ?int $payerId)
    {
        return array_filter($invoiceables, function($invoiceable) use ($payerId) {
            /** @var Invoiceable $invoiceable */
            return $invoiceable->getPayerId() === $payerId;
        });
    }
}