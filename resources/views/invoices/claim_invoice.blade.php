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
    @include('invoices.partials.items_table', ['table_variant' => 'danger'])
@endsection

@section('payments')
    <div class="row">
        <div class="col-md-12">

            <div class="h3">Applied Payments</div>

            <table class="table table-bordered payments-table">
                <thead>
                <tr class="bg-danger">
                    <th>Payment Date</th>
                    <th>Payment Method</th>
                    <th>Total Amount</th>
                    <th>Amount Applied to Invoice</th>
                </tr>
                </thead>
                <tbody>
                @foreach($payments as $payment)
                    <tr>
                        <td>{{ local_date($payment->created_at) }}</td>
                        <td>{{ $payment->getPaymentMethod() }}</td>
                        <td>{{ number_format($payment->amount, 2) }}</td>
                        <td>{{ number_format($payment->amount, 2) }}</td>
                    </tr>
                    @if($payment->notes)
                        <tr>
                            <td colspan="5" style="padding-left: 15px;">
                                <strong>Note: </strong> {{ $payment->notes }}
                            </td>
                        </tr>
                    @endif
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection