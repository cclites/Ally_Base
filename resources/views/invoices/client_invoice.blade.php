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
            <table class="table table-bordered items-table">
                <thead>
                <tr class="bg-info">
                    <th>Service Date</th>
                    <th>Service Name</th>
                    <th>Rate</th>
                    <th>Units</th>
                    <th>Total</th>
                    <th>Amount Due by Payer</th>
                </tr>
                </thead>
                <tbody>
                @foreach($itemGroups as $group => $items)
                    @if($group)
                        <tr>
                            <td colspan="6" class="item-group">
                                <strong>{{ $group }}</strong>
                            </td>
                        </tr>
                    @elseif($itemGroups->count() > 1)
                        <tr>
                            <td colspan="6">
                                <strong>Other Items</strong>
                            </td>
                        </tr>
                    @endif
                    @foreach($items as $item)
                        <?php /** @var \App\Billing\ClientInvoiceItem $item */ ?>
                        <tr>
                            <td>{{ local_date($item->date) }}</td>
                            <td>{{ $item->name }}</td>
                            <td>{{ number_format($item->rate, 2) }}</td>
                            <td>{{ $item->units }}</td>
                            <td>{{ number_format($item->total, 2) }}</td>
                            <td>{{ number_format($item->amount_due, 2) }}</td>
                        </tr>
                    @endforeach
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection

@section('payments')
    <div class="row">
        <div class="col-md-12">

            <h4>Applied Payments</h4>

            <table class="table table-bordered payments-table">
                <thead>
                <tr class="bg-info">
                    <th>Payment Date</th>
                    <th>Payer</th>
                    <th>Payment Method</th>
                    <th>Total Amount</th>
                    <th>Amount Applied to Invoice</th>
                </tr>
                </thead>
                <tbody>
                @foreach($payments as $payment)
                    <tr>
                        <td>{{ local_date($payment->created_at) }}</td>
                        <td>{{ $payment->payer->name ?? '' }}</td>
                        <td>{{ $payment->payment_type }}</td>
                        <td>{{ number_format($payment->amount, 2) }}</td>
                        <td>{{ number_format($payment->pivot->amount_applied ?? '-1', 2) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection