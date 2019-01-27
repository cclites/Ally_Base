<?php
namespace App\Billing\Aggregators;

use App\Billing\ClientInvoice;
use App\Billing\Queries\ClientInvoiceQuery;
use App\Business;
use App\BusinessChain;
use App\Client;
use Illuminate\Support\Collection;

class ClientInvoiceAggregator
{
    /**
     * @var \App\Billing\Queries\ClientInvoiceQuery
     */
    protected $query;

    public function __construct(ClientInvoiceQuery $query)
    {
        $this->query = $query;
    }

    /**
     * @param \App\BusinessChain $chain
     * @return \Illuminate\Support\Collection|ClientInvoice[]
     */
    function getInvoicesByChain(BusinessChain $chain): Collection
    {
        $invoices = $this->query->forBusinessChain($chain)
            ->notPaidInFull()
            ->get();

        return $this->buildPayerInvoices($invoices);
    }

    /**
     * @param \App\Business $business
     * @return \Illuminate\Support\Collection|ClientInvoice[]
     */
    function getInvoicesByBusiness(Business $business): Collection
    {
        $invoices = $this->query->forBusiness($business->id)
            ->notPaidInFull()
            ->get();

        return $this->buildPayerInvoices($invoices);
    }

    /**
     * @param \App\Client $client
     * @return \Illuminate\Support\Collection|ClientInvoice[]
     */
    function getInvoicesByClient(Client $client): Collection
    {
        $invoices = $this->query->where('client_id', $client->id)
            ->notPaidInFull()
            ->get();

        return $this->buildPayerInvoices($invoices);
    }

    /**
     * @param ClientInvoice[] $invoices
     * @return \Illuminate\Support\Collection|PayerInvoices[]
     */
    function buildPayerInvoices(iterable $invoices): Collection
    {
        $payerInvoicesArray = [];
        foreach($invoices as $invoice) {
            $payer = $invoice->getPayer();
            $key = $payer->getUniqueKey();
            if (!isset($payerInvoicesArray[$key])) {
                $payerInvoicesArray[$key] = new PayerInvoices($payer);
            }
            $payerInvoicesArray[$key]->addInvoice($invoice);
        }

        return collect($payerInvoicesArray);
    }



}