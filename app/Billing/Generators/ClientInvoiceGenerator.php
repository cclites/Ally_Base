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


        /**
         * TODO: Faulty logic, need to get the date of the item to determine the effective payer PER item.
         * Past items should not use today's effective payer, we can use the Invoiceable::getItemDate() method.
         * Invoiceables should be grouped by date first, potentially creating a new entity to keep track of items for each payer to ultimately invoice.
         *
         * We also have to account for the scenarios in ClientInvoiceTest
         */

        $invoiceables = $this->getInvoiceables($client);
        $payers = $client->getPayers();
        $allowancePayers = $payers->filter(function($payer) {
            return in_array($payer->payment_allocation, ClientPayer::$allowanceTypes);
        });
        $splitPayers = $payers->filter(function($payer) {
            return $payer->payment_allocation === 'split';
        });
        $balancePayer = $payers->first(function($payer) {
            return $payer->payment_allocation === 'balance';
        });

        DB::beginTransaction();
        $invoices = [];
        foreach($allowancePayers as $allowancePayer) {
            $invoices[] = $this->generateInvoice($client, $allowancePayer, $invoiceables);
        }
        foreach($splitPayers as $splitPayer) {
            $invoices[] = $this->generateInvoice($client, $splitPayer, $invoiceables, $splitPayer->split_percentage);
        }
        if ($balancePayer) {
            $invoices[] = $this->generateInvoice($client, $balancePayer, $invoiceables);
        }
        DB::commit();

        return $invoices;
    }

    /**
     * @param \App\Client $client
     * @return \App\Billing\Contracts\InvoiceableInterface[]
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
     * @param \App\Billing\Contracts\InvoiceableInterface $invoiceable
     * @param float $split
     * @param float $allowance
     * @return array
     */
    public function getItemData(InvoiceableInterface $invoiceable, $split = 1.0, $allowance = 999999.99): array
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
     * @param InvoiceableInterface[] $invoiceables
     * @param float $split
     * @param float $allowance
     * @return \App\Billing\ClientInvoice
     * @throws PayerAllowanceExceeded
     */
    public function generateInvoice(Client $client, ClientPayer $clientPayer, array $invoiceables, float $split = 1.0): ?ClientInvoice
    {
        // Get invoiceables assigned to this payer or on auto
        $invoiceables = array_merge(
            // Order is important here, we should process items specifically assigned to a payer first (re: PayerAllowanceExceeded)
            $this->filterInvoiceablesByPayers($invoiceables, $clientPayer->payer_id),
            $this->filterInvoiceablesByPayers($invoiceables, null)
        );

        $items = [];
        foreach($invoiceables as $invoiceable) {
            /** @var InvoiceableInterface|\Illuminate\Database\Eloquent\Model $invoiceable */
            // Get date and allowance
            $date = Carbon::parse($invoiceable->getItemDate())->toDateString();
            $allowance = $this->getPayerAllowance($clientPayer, $date);

            // Get invoiceable item data
            $data = $this->getItemData($invoiceable, $split, $allowance);

            // Make item and associate invoiceable
            $item = new InvoiceItem($data);
            $item->invoiceable()->associate($invoiceable);
            $items[] = $item;

            // Reduce allowance by the amount due of the item
            $allowance -= $data['amount_due'];

            // Add amount invoiced to invoiceable item and payer
            $invoiceable->addAmountInvoiced($data['amount_due']);
            $clientPayer->addAmountInvoiced($data['amount_due'], $data['date']);

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
     * @param \App\Billing\ClientPayer $clientPayer
     * @param string $date
     * @return float
     */
    public function getPayerAllowance(ClientPayer $clientPayer, string $date): float
    {
        return $clientPayer->getAllowance($date);
    }

    /**
     * @param InvoiceableInterface[] $invoiceables
     * @param int|null $payerId
     * @return InvoiceableInterface[]
     */
    protected function filterInvoiceablesByPayer(array $invoiceables, ?int $payerId)
    {
        return array_filter($invoiceables, function($invoiceable) use ($payerId) {
            /** @var InvoiceableInterface $invoiceable */
            return $invoiceable->getPayerId() === $payerId;
        });
    }
}