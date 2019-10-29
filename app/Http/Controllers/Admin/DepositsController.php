<?php
namespace App\Http\Controllers\Admin;

use App\Billing\Actions\ProcessChainDeposits;
use App\Billing\CaregiverInvoice;
use App\Billing\View\DepositViewGenerator;
use App\Billing\View\Html\HtmlDepositView;
use App\Billing\View\Pdf\PdfDepositView;
use App\Business;
use App\BusinessChain;
use App\Caregiver;
use App\Billing\Deposit;
use App\Http\Controllers\Controller;
use App\Payments\SingleDepositProcessor;
use App\Responses\CreatedResponse;
use App\Responses\ErrorResponse;
use App\Responses\Resources\DepositLog;
use App\Responses\SuccessResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Imports\Worksheet;

class DepositsController extends Controller
{

    /**
     * @var Decimal
     */
    private $amount = 0.00;

    /**
     * miscellaneous variables to assist the import
     */
    private $name;
    private $rows = [];

    /**
     * @var \App\Imports\Worksheet
     */
    public $worksheet;

    /**
     * @var \App\Business
     */
    public $business;

    public function index(Request $request)
    {
        if ($request->expectsJson() && $request->input('json')) {
            $startDate = new Carbon($request->input('start_date') . ' 00:00:00', 'America/New_York');
            $endDate = new Carbon($request->input('end_date') . ' 23:59:59', 'America/New_York');

            // Make UTC to match DB
            $startDate->setTimezone('UTC');
            $endDate->setTimezone('UTC');

            // For a shift search, do not constrain times
            if ($shift_id = $request->input('shift_id')) {
                $startDate = new Carbon('2017-01-01');
                $endDate = Carbon::now();
            }

            $query = Deposit::with(['transaction', 'caregiver', 'business', 'transaction.lastHistory'])
                               ->whereBetween('created_at', [$startDate, $endDate])
                               ->orderBy('created_at', 'DESC');

            if ($shift_id) {
                $query->whereHas('shifts', function($q) use ($shift_id) {
                    $q->where('shifts.id', '=', $shift_id);
                });
            }
            else if ($business = Business::find($request->input('business_id'))) {
                $query->where(function($q) use ($business) {
                    $q->where('business_id', $business->id)
                      ->orWhereIn('caregiver_id', $business->caregivers->pluck('id')->toArray());
                });
            }

            $count = $query->count();
            $limit = 2500;

            if ( $count > $limit ) {

                // Limit deposit return for performance reasons
                return new ErrorResponse( 400, "The number of deposits to display is $count which exceeds the limit of $limit. Please adjust your filters and re-run." );
            }

            return $query->get();
        }
        return view('admin.deposits.index');
    }

    public function show(Deposit $deposit, string $view = "html")
    {
        $strategy = new HtmlDepositView();
        if (strtolower($view) === 'pdf') {
            $strategy = new PdfDepositView('deposit-' . $deposit->id . '.pdf');
        }

        $viewGenerator = new DepositViewGenerator($strategy);
        return $viewGenerator->generate($deposit);
    }

    public function pendingIndex()
    {
        $chains = BusinessChain::ordered()->get();
        return view('admin.deposits.pending', compact('chains'));
    }

    public function processDeposits(BusinessChain $chain, ProcessChainDeposits $action)
    {
        $results = $action->processDeposits($chain);
        $collection = DepositLog::collection($results)->toArray(null);

        return new CreatedResponse('The deposits have been processed.', $collection);
    }

    public function missingBankAccount(Request $request, Business $business)
    {
        return $business->caregivers()->doesntHave('bankAccount')->get();
    }

    public function depositAdjustment()
    {
        return view('admin.deposits.adjustment');
    }

    public function import()
    {
        return view( 'admin.deposits.import' );
    }


    public function processImport(Request $request)
    {
        $request->validate([

            'business_id' => 'required|exists:businesses,id',
            'file'        => 'required|file',
            'type'        => 'required|in:caregiver',
            'notes'       => 'nullable|max:255'
        ]);

        $this->business = Business::findOrFail( $request->business_id );
        $file = $request->file( 'file' )->getPathname();

        $this->worksheet = new Worksheet( $file );

        return $this->handleImport();
    }

    /**
     * 
     * strange issue where the worksheet is not grabbing the correct totalRows.. had to revise the algorithm to work around it
     */
    public function handleImport()
    {
        $aggregation = [];

        for( $rowNo = 2, $totalRows = $this->worksheet->getRowCount(); $rowNo <= $totalRows; $rowNo++ ) {
            // for every row..

            $name   = $this->worksheet->getValue( 'Caregiver Name', $rowNo );
            $amount = $this->worksheet->getValue( 'Expenses', $rowNo );

            if( empty( $name ) || empty( $amount ) ) {
                // that isnt empty..

                continue;
            }

            if( array_key_exists( $name, $aggregation ) ){

                $aggregation[ $name ][ 'amount' ] += $amount;
                $aggregation[ $name ][ 'rows'   ] .= ", $rowNo";
            } else {

                $aggregation[ $name ][ 'name'   ] = $name;
                $aggregation[ $name ][ 'amount' ] = $amount;
                $aggregation[ $name ][ 'rows'   ] = "$rowNo";
            }
        }

        return $aggregation;
    }

    public function manualDeposit(Request $request)
    {
        $request->validate([
            'business_id' => 'required',
            'caregiver_id' => 'nullable|integer',
            'type' => 'required|in:withdrawal,deposit',
            'amount' => 'required|numeric|min:0.1',
            'adjustment' => 'nullable|boolean',
            'notes' => 'nullable|max:1024',
            'process' => 'required|boolean',
        ]);

        $amount = (float) $request->amount;
        if ($request->type == 'withdrawal') {
            $amount = $amount * -1.0;
        }

        if ($request->caregiver_id) {
            $caregiver = Caregiver::findOrFail($request->caregiver_id);

            //For manual deposits, the location id will be included, and trivial to get the chain.
            $chainId =  Business::where('id', $request->business_id)->pluck('chain_id')->first();

            if ($request->process) {
                if (!$caregiver->bankAccount) return new ErrorResponse(400, 'Caregiver does not have a bank account.');
                $transaction = SingleDepositProcessor::depositCaregiver($caregiver, $amount, $request->adjustment ?? false, $request->notes, $chainId);
            } else {
                $invoice = SingleDepositProcessor::generateCaregiverAdjustmentInvoice($caregiver, $amount, $request->notes);
            }
        }
        else if ($request->business_id) {
            $business = Business::findOrFail($request->business_id);

            if ($request->process) {
                if (!$business->bankAccount) return new ErrorResponse(400, 'Business does not have a bank account.');
                $transaction = SingleDepositProcessor::depositBusiness($business, $amount, $request->adjustment ?? false, $request->notes);
            } else {
                $invoice = SingleDepositProcessor::generateBusinessAdjustmentInvoice($business, $amount, $request->notes);
            }
        }

        if (isset($transaction) && $transaction) {
            return new SuccessResponse('Transaction processed for $' . $amount);
        } else if (isset($invoice) && $invoice) {
            return new SuccessResponse('Invoice generated for $' . $amount);
        }
        return new ErrorResponse(400, 'Transaction failure');
    }

    public function failed(Request $request)
    {
        if ($request->expectsJson() && $request->input('json')) {
            $deposits = Deposit::where('success', 0)->with(['transaction', 'transaction.lastHistory', 'caregiver', 'business'])->orderBy('id', 'DESC')->get();
            return $deposits;
        }

        return view('admin.reports.failed_deposits');
    }

    public function markFailed(Deposit $deposit)
    {
        if ($deposit->transaction) {
            $deposit->transaction->recordFailure();
        }
        else {
            $deposit->markFailed();
        }

        return new SuccessResponse('Deposit marked as failed.  This entity has been put on hold. ' . "\n" . 'Once the hold is removed, the related invoice(s) will be eligible for re-deposit.');
    }
}
