<?php

namespace App\Http\Controllers\Caregivers;

use App\Billing\Deposit;
use App\Reports\ShiftsReport;
use App\Shift;
use App\Shifts\AllyFeeCalculator;
use Carbon\Carbon;
use Barryvdh\Snappy\Facades\SnappyPdf as PDF;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class ReportsController extends BaseController
{
    public function deposits()
    {
        return view('caregivers.reports.deposits');
    }

    public function shifts(Request $request)
    {
        if ($request->expectsJson()) {
            $request->validate([
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date',
                'client_id' => 'nullable|exists:clients,id',
            ]);

            $report = new ShiftsReport();
            $report->where('caregiver_id', auth()->user()->id)
                    ->where('checked_out_time', '<>', null)
                    ->orderBy('checked_in_time');
    
            if ($request->has('start_date') || $request->has('end_date')) {
                $timezone = $this->timezone();
                $startDate = new Carbon($request->input('start_date') . ' 00:00:00', $timezone);
                $endDate = new Carbon($request->input('end_date') . ' 23:59:59', $timezone);
                $report->between($startDate, $endDate);
            }

            if ($client_id = $request->input('client_id')) {
                $report->where('client_id', $client_id);
            }

            return $report->rows();
        }

        $clients = auth()->user()->role->clients;
        return view('caregivers.reports.shifts', compact('clients'));
    }

    public function printPaymentHistory($year, $view = "pdf")
    {
        Carbon::setWeekStartsAt(Carbon::MONDAY);

        $caregiver = $this->caregiver();
        $deposits = Deposit::with('shifts')
            ->where('caregiver_id', $caregiver->id)
            ->whereYear('created_at', request()->year)
            ->orderBy('created_at', 'DESC')
            ->get()
            ->map(function ($deposit) {
                $deposit->amount = floatval($deposit->amount);
                $deposit->start = Carbon::instance($deposit->created_at)->subWeek()->startOfWeek()->toDateString();
                $deposit->end = Carbon::instance($deposit->created_at)->subWeek()->endOfWeek()->toDateString();
                return $deposit;
            });

        // TODO: We should not rely on a single business here  (ALLY-431)
        $business = $caregiver->businesses->first();

        if ($view !== "pdf") {
            return view('caregivers.reports.print_payment_history', compact('caregiver', 'deposits', 'business'));
        }

        $pdf = PDF::loadView('caregivers.reports.print_payment_history', compact('caregiver', 'deposits', 'business'));
        $filename = $year . '_year_summary.pdf';
        // return $pdf->download($filename);
        // For mobile app support, download as octet-stream
        return new Response($pdf->output(), 200, array(
            'Content-Type' => 'application/octet-stream',
            'Content-Disposition' =>  'attachment; filename="'.$filename.'"'
        ));
    }

    public function paymentDetails($id)
    {
        $report = new ShiftsReport();
        $report->query()->with('deposits')
            ->whereHas('deposits', function ($query) use ($id) {
                $query->where('deposits.id', $id);
            })
            ->where('caregiver_id', auth()->id());
        $shifts = $report->rows();

        return view('caregivers.reports.payment_details', compact('shifts'));
    }

    public function printPaymentDetails($id)
    {
        $deposit = Deposit::find($id);
        $shifts = $this->getPaymentShifts($id);

        // TODO: We should not rely on a single business here  (ALLY-431)
        $business = $this->caregiver()->businesses->first();

        if (strtolower(request()->type) == 'pdf') {
            $pdf = PDF::loadView('caregivers.print.payment_details', compact('business', 'shifts', 'deposit'))->setOrientation('landscape');
            $filename = 'deposit_details.pdf';
            // return $pdf->download($filename);
            // For mobile app support, download as octet-stream
            return new Response($pdf->output(), 200, array(
                'Content-Type' => 'application/octet-stream',
                'Content-Disposition' =>  'attachment; filename="'.$filename.'"'
            ));
        }

        return view('caregivers.print.payment_details', compact('business', 'shifts', 'deposit'));
    }

    protected function getPaymentShifts($id)
    {
        $shifts = Shift::with('deposits', 'activities')
            ->whereHas('deposits', function ($query) use ($id) {
                $query->where('deposits.id', $id);
            })
            ->where('caregiver_id', auth()->id())
            ->orderBy('checked_in_time')
            ->get()
            ->map(function ($shift) {
                $allyFee = AllyFeeCalculator::getHourlyRate($shift->client, null, $shift->caregiver_rate, $shift->provider_fee);
                $row = (object) collect($shift->toArray())
                    ->merge([
                        'hours' => $shift->duration(),
                        'ally_fee' => number_format($allyFee, 2),
                        'hourly_total' => number_format($shift->caregiver_rate + $shift->provider_fee + $allyFee, 2),
                        'mileage_costs' => number_format($shift->costs()->getMileageCost(), 2),
                        'caregiver_total' => number_format($shift->costs()->getCaregiverCost(), 2),
                        'provider_total' => number_format($shift->costs()->getProviderFee(), 2),
                        'ally_total' => number_format($shift->costs()->getAllyFee(), 2),
                        'ally_pct' => AllyFeeCalculator::getPercentage($shift->client, null),
                        'shift_total' => number_format($shift->costs()->getTotalCost(), 2),
                        'confirmed' => $shift->statusManager()->isConfirmed(),
                        'status' => $shift->status ? Str::title(preg_replace('/_/', ' ', $shift->status)) : '',
                        'EVV' => $shift->verified,
                    ])->toArray();

                $row->checked_in_time = Carbon::parse($row->checked_in_time);
                $row->checked_out_time = Carbon::parse($row->checked_out_time);
                $row->activities = collect($row->activities)->sortBy('name');

                return $row;
            });

        return $shifts;
    }
}
