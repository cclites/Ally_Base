<?php
namespace App\Http\Controllers\Admin;

use App\Billing\BusinessInvoice;
use App\Billing\CaregiverInvoice;
use App\Billing\Contracts\DepositInvoiceInterface;
use App\Billing\Generators\BusinessInvoiceGenerator;
use App\Billing\Generators\CaregiverInvoiceGenerator;
use App\Billing\Queries\BusinessInvoiceQuery;
use App\Billing\Queries\CaregiverInvoiceQuery;
use App\Billing\View\InvoiceViewFactory;
use App\Billing\View\InvoiceViewGenerator;
use App\BusinessChain;
use App\Http\Controllers\Controller;
use App\Responses\CreatedResponse;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Responses\Resources\DepositInvoice as DepositInvoiceResponse;

class DepositInvoiceController extends Controller
{
    public function index(Request $request, CaregiverInvoiceQuery $caregiverInvoiceQuery, BusinessInvoiceQuery $businessInvoiceQuery)
    {
        if ($request->filled('json')) {

            if ($request->filled('paid')) {
                if ($request->paid) {
                    $caregiverInvoiceQuery->paidInFull();
                    $businessInvoiceQuery->paidInFull();
                } else {
                    $caregiverInvoiceQuery->notPaidInFull();
                    $businessInvoiceQuery->notPaidInFull();
                }
            }

            if ( $chainId = $request->input( 'chain_id' ) ) {

                $chain = BusinessChain::findOrFail( $chainId );
                $caregiverInvoiceQuery->forBusinessChain( $chain );
                $businessInvoiceQuery->forBusinessChain( $chain );
            }

            if ( $request->has( 'start_date' ) ) {
                $startDate = Carbon::parse( $request->start_date )->toDateTimeString();
                $endDate   = Carbon::parse( $request->end_date )->toDateString() . ' 23:59:59';
                $caregiverInvoiceQuery->whereBetween( 'created_at', [ $startDate, $endDate ] );
                $businessInvoiceQuery->whereBetween( 'created_at', [ $startDate, $endDate ] );
            }

            $caregiverInvoiceQuery->with(['caregiver', 'caregiver.businessChains']);
            $businessInvoiceQuery->with(['business', 'business.chain']);

            $count = (clone $caregiverInvoiceQuery)->count() + (clone $businessInvoiceQuery)->count();
            $limit = 5000;
            if ( $count > $limit ) {
                // Limit deposit return for performance reasons
                return new ErrorResponse(400, "The number of deposits to display is $count which exceeds the limit of $limit. Please adjust your filters and re-run.");
            }

            $caregiverInvoices = $caregiverInvoiceQuery->get();
            $businessInvoices = $businessInvoiceQuery->get();
            $invoices = $caregiverInvoices->merge($businessInvoices);

            return DepositInvoiceResponse::collection($invoices);
        }

        $chains = BusinessChain::ordered()->get();
        return view_component('admin-deposit-invoices', 'Deposit Stubs', compact('chains'));
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
            $generator = clone $businessInvoiceGenerator;
            try {
                $invoices[] = $generator->generate($business);
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
            $generator = clone $caregiverInvoiceGenerator;
            try {
                $invoices[] = $generator->generate($caregiver);
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

    public function showBusinessInvoice(BusinessInvoice $invoice, string $view = InvoiceViewFactory::HTML_VIEW)
    {
        $viewGenerator = $this->getViewGenerator($invoice, $view);
        return $viewGenerator->generateBusinessInvoice($invoice);
    }

    public function showCaregiverInvoice(CaregiverInvoice $invoice, string $view = InvoiceViewFactory::HTML_VIEW)
    {
        $viewGenerator = $this->getViewGenerator($invoice, $view);
        return $viewGenerator->generateCaregiverInvoice($invoice);
    }

    public function destroyBusinessInvoice(BusinessInvoice $invoice)
    {
        if ($invoice->deposits()->exists()) {
            return new ErrorResponse(400, 'This invoice cannot be removed because it has deposits assigned.');
        }

        if ($invoice->delete()) {
            return new SuccessResponse("The invoice has been removed.");
        }

        return new ErrorResponse(500, "The invoice could not be removed.");
    }

    public function update( Request $request, $id, $type )
    {
        switch( $type ){

            case 'caregiver_invoices':

                $invoice = CaregiverInvoice::find( $id );
                break;
            case 'business_invoices':

                $invoice = BusinessInvoice::find( $id );
                break;
            default:

                return new ErrorResponse( 500, 'Invalid invoice type.' );
                break;
        }
        $data = $request->validate([

            'notes' => 'required|max:255'
        ]);

        if( $invoice->update( $request->toArray() ) ) return new SuccessResponse( 'invoice has been updatd.');

        return new ErrorResponse( 500, 'invoice could not be updated.');

    }

    public function destroyCaregiverInvoice(CaregiverInvoice $invoice)
    {
        if ($invoice->deposits()->exists()) {
            return new ErrorResponse(400, 'This invoice cannot be removed because it has deposits assigned.');
        }

        if ($invoice->delete()) {
            return new SuccessResponse("The invoice has been removed.");
        }

        return new ErrorResponse(500, "The invoice could not be removed.");
    }

    private function getViewGenerator(DepositInvoiceInterface $invoice, string $view)
    {
        $strategy = InvoiceViewFactory::create($invoice, $view);
        $viewGenerator = new InvoiceViewGenerator($strategy);
        return $viewGenerator;
    }
}