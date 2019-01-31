<?php


namespace App\Billing\Queries;


use App\Billing\Invoiceable\InvoiceableModel;
use Illuminate\Database\Eloquent\Model;

class InvoiceableQuery extends BaseQuery
{
    /**
     * @var \App\Billing\Invoiceable\InvoiceableModel
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

    function hasPaidClientInvoice(): self
    {
        $this->whereExists(function($query) {
            $this->clientInvoiceSubquery($query);
            $query->whereColumn('client_invoices.amount_paid', '=', 'client_invoices.amount');
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

    function hasPaidCaregiverInvoice(): self
    {
        $this->whereExists(function($query) {
            $this->caregiverInvoiceSubquery($query);
            $query->whereColumn('caregiver_invoices.amount_paid', '=', 'caregiver_invoices.amount');
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

    function hasPaidBusinessInvoice(): self
    {
        $this->whereExists(function($query) {
            $this->businessInvoiceSubquery($query);
            $query->whereColumn('business_invoices.amount_paid', '=', 'business_invoices.amount');
        });

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