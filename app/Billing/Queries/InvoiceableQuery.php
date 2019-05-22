<?php


namespace App\Billing\Queries;


use App\Billing\Invoiceable\InvoiceableModel;
use App\Billing\Invoiceable\ShiftExpense;
use App\Shift;
use Illuminate\Database\Eloquent\Model;

class InvoiceableQuery extends BaseQuery
{
    use BelongsToBusinessesQueries;

    /**
     * @var \App\Billing\Invoiceable\InvoiceableModel
     * The entity type that is being queried
     */
    protected $invoiceableModel;

    function __construct(InvoiceableModel $invoiceableModel)
    {
        $this->invoiceableModel = $invoiceableModel;
        parent::__construct();
    }

    function getModelInstance(): Model
    {
        return clone $this->invoiceableModel;
    }

    function forCaregivers(array $caregiverIds): self
    {
        parent::forCaregivers($caregiverIds);

        return $this;
    }

    function forInvoiceable(InvoiceableModel $invoiceable): self
    {
        $this->where($this->invoiceableModel->getKeyName(), '=', $invoiceable->getKey());

        return $this;
    }

    function doesntHaveClientInvoice(): self
    {
        $this->whereNotExists(function($query) {
            $this->clientInvoiceSubquery($query);
        });

        return $this;
    }

    function hasClientInvoice(): self
    {
        $this->whereExists(function($query) {
           $this->clientInvoiceSubquery($query);
        });

        return $this;
    }

    function hasClientInvoicesPaid(): self
    {
        $this->hasClientInvoice();
        $this->whereNotExists(function($query) {
            $this->clientInvoiceSubquery($query);
            $query->whereColumn('client_invoices.amount_paid', '!=', 'client_invoices.amount');
        });

        return $this;
    }

    function doesntHaveCaregiverInvoice(): self
    {
        $this->whereNotExists(function($query) {
            $this->caregiverInvoiceSubquery($query);
        });

        return $this;
    }

    function hasCaregiverInvoice(): self
    {
        $this->whereExists(function($query) {
            $this->caregiverInvoiceSubquery($query);
        });

        return $this;
    }

    function hasCaregiverInvoicesPaid(): self
    {
        $this->hasCaregiverInvoice();
        $this->whereNotExists(function($query) {
            $this->caregiverInvoiceSubquery($query);
            $query->whereColumn('caregiver_invoices.amount_paid', '!=', 'caregiver_invoices.amount');
        });

        return $this;
    }

    function doesntHaveBusinessInvoice(): self
    {
        $this->whereNotExists(function($query) {
            $this->businessInvoiceSubquery($query);
        });

        return $this;
    }

    function hasBusinessInvoice(): self
    {
        $this->whereExists(function($query) {
            $this->businessInvoiceSubquery($query);
        });

        return $this;
    }

    function hasBusinessInvoicesPaid(): self
    {
        $this->hasBusinessInvoice();
        $this->whereNotExists(function($query) {
            $this->businessInvoiceSubquery($query);
            $query->whereColumn('business_invoices.amount_paid', '!=', 'business_invoices.amount');
        });

        return $this;
    }

    /**
     * This is a method used to exclude shifts (and related expenses) marked as PAID prior to the billing revamp in February 2019
     *
     * @return \App\Billing\Queries\InvoiceableQuery
     */
    function notBelongingToAnOldFinalizedShift(): self
    {
        if ($this->invoiceableModel instanceof Shift) {
            $this->where('status', '!=', 'PAID');
        }
        else if ($this->invoiceableModel instanceof ShiftExpense) {
            $this->whereDoesntHave('shift', function($q) {
                $q->where('status', 'PAID');
            });
        }

        return $this;
    }

    protected function clientInvoiceSubquery($query)
    {
        $query->from("client_invoice_items");
        $query->join('client_invoices',
            'client_invoices.id', '=', 'client_invoice_items.invoice_id');
        $query->where("client_invoice_items.invoiceable_type", maps_from_model($this->invoiceableModel))
            ->whereColumn("client_invoice_items.invoiceable_id", $this->invoiceableModel->getTable() . "." . $this->invoiceableModel->getKeyName());
    }

    protected function caregiverInvoiceSubquery($query)
    {
        $query->from("caregiver_invoice_items");
        $query->join('caregiver_invoices',
            'caregiver_invoices.id', '=', 'caregiver_invoice_items.invoice_id');
        $query->where("caregiver_invoice_items.invoiceable_type", maps_from_model($this->invoiceableModel))
            ->whereColumn("caregiver_invoice_items.invoiceable_id", $this->invoiceableModel->getTable() . "." . $this->invoiceableModel->getKeyName());
    }

    protected function businessInvoiceSubquery($query)
    {
        $query->from("business_invoice_items");
        $query->join('business_invoices',
            'business_invoices.id', '=', 'business_invoice_items.invoice_id');
        $query->where("business_invoice_items.invoiceable_type", maps_from_model($this->invoiceableModel))
            ->whereColumn("business_invoice_items.invoiceable_id", $this->invoiceableModel->getTable() . "." . $this->invoiceableModel->getKeyName());
    }
}