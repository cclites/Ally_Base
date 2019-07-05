<?php
namespace App\Http\Controllers\Business;

use App\Billing\BusinessInvoice;
use App\Billing\BusinessInvoiceItem;
use App\Billing\ClientInvoice;
use App\Billing\ClientPayer;
use App\Billing\ClientInvoiceItem;
use App\Billing\Payer;
use App\Billing\Deposit;
use App\Billing\Payment;
use App\Billing\View\DepositViewGenerator;
use App\Billing\View\Excel\ExcelDepositView;
use App\Billing\View\Excel\ExcelPaymentView;
use App\Billing\View\Html\HtmlDepositView;
use App\Billing\View\Html\HtmlPaymentView;
use App\Billing\View\PaymentViewGenerator;
use App\Billing\View\Pdf\PdfDepositView;
use App\Billing\View\Pdf\PdfPaymentView;
use App\Business;
use Illuminate\Support\Collection;

use Log;
class StatementController extends BaseController
{
    public function itemizePayment(Payment $payment)
    {
        $invoices = $payment->invoices()->with([
            'client',
            'items',
            'items.invoiceable',
        ])->get();

        $items = $invoices->reduce(function(Collection $collection, ClientInvoice $invoice) {
            return $invoice->items->reduce(function(Collection $collection, ClientInvoiceItem $item) use ($invoice) {
                return $collection->push(PaymentItemData::fromInvoiceItem($invoice, $item));
            }, $collection);
        }, new Collection());

        foreach($items as $item){

            $payerId = ClientPayer::where('id', $item->invoice["client_payer_id"])->pluck('payer_id');

            if(filled($payerId)){
                $item->payer = Payer::where('id', $payerId)->pluck('name')->first();
            }

            $clientType = $item->client["client_type"];
            $item->client_type = ucfirst(str_replace("_", " ", $clientType));
            $item->businesses = Business::where('id', $item->client["business_id"])->get();
        }


        return view_component(
            'itemized-payment',
            'Itemized Payment Details',
            compact('invoices', 'payment', 'items'),
            ['Reconciliation Report' => route('business.reports.reconciliation')]
        );
    }

    public function itemizeDeposit(Deposit $deposit)
    {
        $invoices = $deposit->businessInvoices()->with([
            'items',
            'items.invoiceable',
        ])->get();
        $items = $invoices->reduce(function(Collection $collection, BusinessInvoice $invoice) {
            return $invoice->items->reduce(function(Collection $collection, BusinessInvoiceItem $item) {
                return $collection->push(DepositItemData::fromBusinessItem($item));
            }, $collection);
        }, new Collection());

        foreach($items as $item){

            /*
            $payerId = ClientPayer::where('id', $item->invoice["client_payer_id"])->pluck('payer_id');
            if(filled($payerId)){
                $item->payer = Payer::where('id', $payerId)->pluck('name')->first();
            }
            */

            $clientType = $item->client["client_type"];
            $item->client_type = ucfirst(str_replace("_", " ", $clientType));
            $item->business = Business::where('id', $item->client["business_id"])->pluck('name')->first();
        }

        return view_component(
            'itemized-deposit',
            'Itemized Deposit Details',
            compact('invoices', 'deposit', 'items'),
            ['Reconciliation Report' => route('business.reports.reconciliation')]
        );
    }

    public function payment(Payment $payment, string $view = "html")
    {
        $this->authorize('read', $payment);

        $strategy = new HtmlPaymentView();
        if (strtolower($view) === 'pdf') {
            $strategy = new PdfPaymentView('payment-' . $payment->id . '.pdf');
        }
        if (strtolower($view) === 'xls') {
            $strategy = new ExcelPaymentView('payment-items-' . $payment->id . '.xls');
        }

        $viewGenerator = new PaymentViewGenerator($strategy);
        return $viewGenerator->generate($payment);
    }

    public function deposit(Deposit $deposit, string $view = "html")
    {
        $this->authorize('read', $deposit);

        $strategy = new HtmlDepositView();
        if (strtolower($view) === 'pdf') {
            $strategy = new PdfDepositView('deposit-' . $deposit->id . '.pdf');
        }
        if (strtolower($view) === 'xls') {
            $strategy = new ExcelDepositView('deposit-items-' . $deposit->id . '.xls');
        }

        $viewGenerator = new DepositViewGenerator($strategy);
        return $viewGenerator->generate($deposit);
    }
}

class DepositItemData {
    /** @var \App\Client|null */
    public $client;
    /** @var \App\Caregiver|null */
    public $caregiver;
    /** @var \App\Shift|null */
    public $shift;
    /** @var float */
    public $client_rate;
    /** @var float */
    public $caregiver_rate;
    /** @var float */
    public $ally_rate;
    /** @var float */
    public $provider_rate;
    /** @var float */
    public $rate;
    /** @var float */
    public $units;
    /** @var float */
    public $total;
    /** @var string */
    public $group;
    /** @var string */
    public $name;

    public static function fromBusinessItem(BusinessInvoiceItem $item): self
    {
        /** @var \App\Billing\Contracts\InvoiceableInterface $invoiceable */
        $invoiceable = optional($item->getInvoiceable());

        $data = new self();
        $data->client = $invoiceable->getClient();
        $data->caregiver = $invoiceable->getCaregiver();
        $data->shift = $invoiceable->getShift();
        $data->client_rate = $item->client_rate;
        $data->caregiver_rate = $item->caregiver_rate;
        $data->ally_rate = $item->ally_rate;
        $data->provider_rate = $item->rate;
        $data->rate = $item->rate;
        $data->units = $item->units;
        $data->total = $item->total;
        $data->group = $item->group;
        $data->name = $item->name;
        $data->date = $item->date ?? optional($item->invoice)->created_at->toDateTimeString();

        return $data;
    }
}

class PaymentItemData {
    /** @var array */
    public $invoice;
    /** @var \App\Client|null */
    public $client;
    /** @var \App\Caregiver|null */
    public $caregiver;
    /** @var \App\Shift|null */
    public $shift;
    /** @var float */
    public $client_rate;
    /** @var float */
    public $caregiver_rate;
    /** @var float */
    public $ally_rate;
    /** @var float */
    public $provider_rate;
    /** @var float */
    public $rate;
    /** @var float */
    public $units;
    /** @var float */
    public $total;
    /** @var float */
    public $amount_due;
    /** @var string */
    public $group;
    /** @var string */
    public $name;

    public static function fromInvoiceItem(ClientInvoice $invoice, ClientInvoiceItem $item): self
    {
        /** @var \App\Billing\Contracts\InvoiceableInterface $invoiceable */
        $invoiceable = optional($item->getInvoiceable());

        $data = new self();
        $data->invoice = $invoice->attributesToArray(); // to avoid passing all related data
        $data->client = $invoice->client;
        $data->caregiver = $invoiceable->getCaregiver();
        $data->shift = $invoiceable->getShift();
        $data->rate = $item->rate;
        $data->units = $item->units;
        $data->total = $item->total;
        $data->group = $item->group;
        $data->name = $item->name;
        $data->amount_due = $item->amount_due;
        $data->date = $item->date ?? $data->invoice['created_at'];

        return $data;
    }
}