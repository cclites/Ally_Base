<?php

namespace App\Http\Controllers\Business;

use App\GatewayTransaction;
use App\Reports\ShiftsReport;
use App\Responses\ErrorResponse;
use Illuminate\Http\Request;

class TransactionController extends BaseController
{
    public function show(GatewayTransaction $transaction)
    {
        $transaction->load(['payment', 'deposit', 'history']);
        if (!$this->hasAccessTo($transaction)) {
            return new ErrorResponse(403, 'You do not have access to this transaction.');
        }

        $report = new ShiftsReport();
        $report->where('business_id', $this->business()->id);
        $report->forTransaction($transaction);
        $shifts = $report->rows();

        $client_summary = $shifts->groupBy('client_id')->map(function ($client_group) {
                return collect([
                    'name' => $client_group->first()['client']['name'],
                    'cg_total' => $client_group->sum('caregiver_total'),
                    'hours' => $client_group->sum('duration'),
                    'ally_total' => $client_group->sum('ally_total'),
                    'provider_total' => $client_group->sum('provider_total'),
                    'total' => $client_group->sum('shift_total')
                ]);
            })
            ->sortBy('name')
            ->values();

        $caregiver_summary = $shifts->groupBy('caregiver_id')
            ->map(function ($cg_group) {
                return collect([
                    'name' => $cg_group->first()['caregiver']['name'],
                    'hours' => $cg_group->sum('duration'),
                    'total' => $cg_group->sum('caregiver_total')
                ]);
            })
            ->sortBy('name')
            ->values();

        return view('business.transactions.show', compact(
            'transaction',
            'shifts',
            'client_summary',
            'caregiver_summary'
        ));
    }

    protected function hasAccessTo(GatewayTransaction $transaction)
    {
        if ($transaction->payment) {
            return ($transaction->payment->business_id == $this->business()->id);
        }

        if ($transaction->deposit) {
            return ($transaction->deposit->business_id == $this->business()->id);
        }

        return false;
    }
}
