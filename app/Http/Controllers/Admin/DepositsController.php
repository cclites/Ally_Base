<?php
namespace App\Http\Controllers\Admin;

use App\Business;
use App\Caregiver;
use App\Deposit;
use App\Http\Controllers\Controller;
use App\Payments\DepositProcessor;
use App\Payments\SingleDepositProcessor;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DepositsController extends Controller
{
    public function index(Request $request)
    {
        if ($request->expectsJson() && $request->input('json')) {
            $startDate = new Carbon($request->input('start_date') . ' 00:00:00', 'America/New_York');
            $endDate = new Carbon($request->input('end_date') . ' 23:59:59', 'America/New_York');

            // Make UTC to match DB
            $startDate->setTimezone('UTC');
            $endDate->setTimezone('UTC');

            $query = Deposit::with(['transaction', 'caregiver', 'business', 'transaction.lastHistory'])
                               ->whereBetween('created_at', [$startDate, $endDate])
                               ->orderBy('created_at', 'DESC');

            if ($business = Business::find($request->input('business_id'))) {
                $query->where(function($q) use ($business) {
                    $q->where('business_id', $business->id)
                      ->orWhereIn('caregiver_id', $business->caregivers->pluck('id')->toArray());
                });
            }

            return $query->get();
        }
        return view('admin.deposits.index');
    }

    public function pendingIndex()
    {
        return view('admin.deposits.pending');
    }

    public function pendingDeposits(Request $request, Business $business)
    {
        $startDate = new Carbon($request->input('start_date') . ' 00:00:00', 'America/New_York');
        $endDate = new Carbon($request->input('end_date') . ' 23:59:59', 'America/New_York');

        $processor = new DepositProcessor($business, $startDate, $endDate);
        return $processor->getDepositData();
    }

    public function missingBankAccount(Request $request, Business $business)
    {
        return $business->caregivers()->doesntHave('bankAccount')->get();
    }

    public function deposit(Request $request, Business $business)
    {
        $startDate = new Carbon($request->input('start_date') . ' 00:00:00', 'America/New_York');
        $endDate = new Carbon($request->input('end_date') . ' 23:59:59', 'America/New_York');

        $processor = new DepositProcessor($business, $startDate, $endDate);
        $count = $processor->process();
        return new SuccessResponse('There were ' . $count . ' successful transactions.');
    }

    public function depositAdjustment()
    {
        return view('admin.deposits.adjustment');
    }

    public function manualDeposit(Request $request)
    {
        $request->validate([
            'business_id' => 'required_without:caregiver_id',
            'caregiver_id' => 'required_without:business_id',
            'type' => 'required|in:withdrawal,deposit',
            'amount' => 'required|numeric|min:0.1',
            'adjustment' => 'nullable|boolean',
            'notes' => 'nullable|max:1024',
        ]);

        $amount = (float) $request->amount;
        if ($request->type == 'withdrawal') {
            $amount = $amount * -1.0;
        }

        if ($request->caregiver_id) {
            $caregiver = Caregiver::findOrFail($request->caregiver_id);
            if (!$caregiver->bankAccount) return new ErrorResponse(400, 'Caregiver does not have a bank account.');
            $transaction = SingleDepositProcessor::depositCaregiver($caregiver, $amount, $request->adjustment ?? false, $request->notes);
        }
        else if ($request->business_id) {
            $business = Business::findOrFail($request->business_id);
            if (!$business->bankAccount) return new ErrorResponse(400, 'Business does not have a bank account.');
            $transaction = SingleDepositProcessor::depositBusiness($business, $amount, $request->adjustment ?? false, $request->notes);
        }

        if ($transaction) {
            return new SuccessResponse('Transaction processed for $' . $amount);
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

    public function markSuccessful(Deposit $deposit)
    {
        if ($deposit->transaction) {
            $deposit->transaction->update(['success' => true]);
        }
        $deposit->update(['success' => true]);
        foreach($deposit->shifts as $shift) {
            if ($deposit->caregiver) {
                $shift->statusManager()->ackCaregiverDeposit();
            }
            else if ($deposit->business) {
                $shift->statusManager()->ackBusinessDeposit();
            }
        }
        return new SuccessResponse('Deposit marked as successful.');
    }

    public function markFailed(Deposit $deposit)
    {
        if ($deposit->transaction) {
            $deposit->transaction->update(['success' => false]);
        }
        $deposit->update(['success' => false]);
        foreach($deposit->shifts as $shift) {
            if ($deposit->caregiver) {
                $shift->statusManager()->ackReturnedCaregiverDeposit();
            }
            else if ($deposit->business) {
                $shift->statusManager()->ackReturnedBusinessDeposit();
            }
        }
        return new SuccessResponse('Deposit marked as failed.');
    }
}
