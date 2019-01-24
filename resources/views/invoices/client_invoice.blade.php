<?php
/**
 * @var \App\Billing\ClientInvoice $invoice
 * @var \App\Contracts\ContactableInterface $sender
 * @var \App\Contracts\ContactableInterface $recipient
 * @var \Illuminate\Support\Collection $itemGroups
 * @var \Illuminate\Support\Collection|App\Billing\Payment[] $payments
 */
?>
@extends('invoices.layout')

@section('items')
    <div class="row">
        <div class="col-md-12">
            <table class="table table-bordered shifts-table">
                <tbody>
                <tr class="bg-info">
                    <th>Date</th>
                    <th>Service Name</th>
                    <th>Rate</th>
                    <th>Units</th>
                    <th>Total</th>
                    <th>Amount Due by Payer</th>
                </tr>
                @if ($report_type != 'notes' && isset($payment) && $payment->adjustment)
                    <tr>
                        <td>{{ $payment->created_at->setTimezone($timezone)->format('m/d/Y') }}</td>
                        <td>Manual Adjustment</td>
                        <td colspan="3">{{ $payment->notes }}</td>
                        <td>${{ $payment->amount }}</td>
                    </tr>
                @endif
                @foreach($shifts as $shift)
                    <tr >
                        <td>
                            {{ $shift->checked_in_time->setTimezone($timezone)->format('m/d/Y') }}
                        </td>
                        <td style="white-space: nowrap">
                            {{ $shift->checked_in_time->setTimezone($timezone)->format('g:ia') }} -
                            {{ $shift->checked_out_time->setTimezone($timezone)->format('g:ia') }}
                        </td>
                        <td>{{ $shift->verified ? 'Yes' : 'No' }}</td>
                        <td>
                            {{ $shift->activities->implode('name', ', ') }}
                        </td>
                        <td>
                            {{ $shift->caregiver_name ?? $shift->caregiver->name }}
                        </td>
                        @if ($report_type != 'notes')
                            <td>
                                &dollar;{{ number_format($shift->hourly_total ?? $shift->costs()->getTotalHourlyCost(), 2) }}
                            </td>
                        @endif
                        <td>
                            {{ $shift->hours ?? $shift->duration }}
                        </td>
                        @if ($report_type != 'notes')
                            <td>
                                &dollar;{{ number_format($shift->mileage_costs ?? $shift->costs()->getMileageCost(), 2) }}
                            </td>
                            <td>
                                &dollar;{{ number_format($shift instanceof \App\Shift ? $shift->costs()->getOtherExpenses() : $shift->other_expenses, 2) }}
                            </td>
                            <td>
                                &dollar;{{ number_format($shift->shift_total ?? $shift->costs()->getTotalCost(), 2) }}
                            </td>
                        @endif
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection

@section('payments')

@endsection