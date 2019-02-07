<?php
namespace App\Http\Controllers\Admin;

use App\Billing\BusinessInvoice;
use App\Billing\CaregiverInvoice;
use App\Billing\Contracts\DepositInvoiceInterface;
use App\Billing\Generators\BusinessInvoiceGenerator;
use App\Billing\Generators\CaregiverInvoiceGenerator;
use App\Billing\Queries\BusinessInvoiceQuery;
use App\Billing\Queries\CaregiverInvoiceQuery;
use App\Billing\View\HtmlViewStrategy;
use App\Billing\View\InvoiceViewGenerator;
use App\Billing\View\PdfViewStrategy;
use App\BusinessChain;
use App\Http\Controllers\Controller;
use App\Responses\CreatedResponse;
use Illuminate\Http\Request;
use App\Responses\Resources\DepositInvoice as DepositInvoiceResponse;

class DepositInvoiceController extends Controller
{
    public function index(Request $request, CaregiverInvoiceQuery $caregiverInvoiceQuery, BusinessInvoiceQuery $businessInvoiceQuery)
    {
        if ($request->has('paid')) {
            if ($request->paid) {
                $caregiverInvoiceQuery->paidInFull();
                $businessInvoiceQuery->paidInFull();
            } else {
                $caregiverInvoiceQuery->notPaidInFull();
                $businessInvoiceQuery->notPaidInFull();
            }
        }

        if ($chainId = $request->input('chain_id')) {
            $chain = BusinessChain::findOrFail($chainId);
            $caregiverInvoiceQuery->forBusinessChain($chain);
            $caregiverInvoiceQuery->forBusinessChain($chain);
        }

        $caregiverInvoices = $caregiverInvoiceQuery->with(['caregiver'])->get();
        $businessInvoices = $businessInvoiceQuery->with(['business'])->get();

        return DepositInvoiceResponse::collection($caregiverInvoices->merge($businessInvoices));
    }

    public function generate(Request $request, BusinessInvoiceGenerator $businessInvoiceGenerator, CaregiverInvoiceGenerator $caregiverInvoiceGenerator)
    {
        $request->validate([
            'chain_id' => 'required|exists:business_chains,id',
        ]);

        \DB::beginTransaction();

        $chain = BusinessChain::findOrFail($request->input('chain_id'));
        $invoices = [];
        $errors = [];
        foreach($chain->businesses as $business) {
            try {
                $invoices[] = $businessInvoiceGenerator->generate($business);
            }
            catch(\Exception $e) {
                $errors[] = [
                    'recipient' => $business->name(),
                    'exception' => get_class($e),
                    'message' => $e->getMessage(),
                ];
            }
        }

        /** @var \App\Caregiver[] $caregivers */
        $caregivers = $chain->caregivers()->active()->get();
        foreach($caregivers as $caregiver) {
            try {
                $invoices[] = $caregiverInvoiceGenerator->generate($caregiver);
            }
            catch(\Exception $e) {
                $errors[] = [
                    'recipient' => $caregiver->name(),
                    'exception' => get_class($e),
                    'message' => $e->getMessage(),
                ];
            }
        }

        \DB::commit();

        $invoices = array_filter($invoices); // remove null entries when no invoice was generated

        return new CreatedResponse(count($invoices) . ' invoices were created.', [
            'invoices' => $invoices,
            'errors' => $errors,
        ]);
    }

    public function showBusinessInvoice(BusinessInvoice $invoice, string $view = "html")
    {
        $viewGenerator = $this->getViewGenerator($invoice, $view);
        return $viewGenerator->generateBusinessInvoice($invoice);
    }

    public function showCaregiverInvoice(CaregiverInvoice $invoice, string $view = "html")
    {
        $viewGenerator = $this->getViewGenerator($invoice, $view);
        return $viewGenerator->generateCaregiverInvoice($invoice);
    }

    private function getViewGenerator(DepositInvoiceInterface $invoice, string $view)
    {
        $strategy = new HtmlViewStrategy();
        if (strtolower($view) === 'pdf') {
            $strategy = new PdfViewStrategy('invoice-' . str_slug($invoice->getName()) . '.pdf');
        }

        $viewGenerator = new InvoiceViewGenerator($strategy);
        return $viewGenerator;
    }
}