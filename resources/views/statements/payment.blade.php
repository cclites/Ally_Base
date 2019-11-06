<?php
/**
 * @var \App\Billing\View\Data\PaymentInvoiceData[] $invoiceObjects
 * @var \App\Billing\Payment $payment
 */
?>
@extends('layouts.print')

@section('title', 'Payment #' . $payment->id)

@push('head')
    <style>
        body {
            color: #000!important;
            font-size: 1.4rem;
            font-weight: 500;
        }
        small { font-weight: 500 }

        .header-left,
        .footer-left {
            float: left;
            width: 65%;
            padding-left: 0;
        }

        .header-right,
        .footer-right {
            float: left;
            width: 35%;
            padding-right: 0;
        }

        .footer-left {
            padding-left: 2rem;
            padding-right: 4rem;
        }

        .header-right table tr td {
            padding-left: .5rem;
        }

        .shifts-table {
            margin-top: 2rem;
            font-size: 1.4rem;
        }

        .bg-info {
            color: white;
            background-color: #1e88e5!important;
        }

        .header-right-table {
            float: right;
        }

        .header-right-table td,
        .header-right-table th {
            text-align: left;
            padding: .5rem .75rem;
        }

        .print-header {
            margin: 0;
            padding: 15px;
        }

        .logo img {
            max-height: 80px;
        }

        .items-table td, .items-table th {
            padding-top: 6px !important;
            padding-bottom: 6px !important;
        }

        .items-table td.item-group {
            padding-top: 4px !important;
            padding-bottom: 4px !important;
        }
    </style>
@endpush

@section('content')
    <div class="">
        <div class="row print-header">
            <div class="header-left">
                @include('layouts.partials.print_logo')
                <br>
                <table>
                    <tr>
                        <td colspan="2">
                            <strong>{{ $payer->name() }}</strong>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            @include('invoices.partials.address', ['address' => $payer->getAddress(), 'phone' => $payer->getPhoneNumber()])
                        </td>
                    </tr>
                </table>
            </div>
            <div class="text-right header-right">
                <div class="h3">Payment #{{ $payment->id }}</div>
                <br>
                <table class="header-right-table">
                    <tr>
                        <td>Payment Date</td>
                        <td>{{ $payment->created_at->format('m/d/Y') }}</td>
                    </tr>
                    <tr>
                        <th>Payment Amount:</th>
                        <td>
                            &dollar;{{ number_format($payment->getAmount(), 2) }}
                        </td>
                    </tr>
                    <tr>
                        <td>Payment Status:</td>
                        <td>
                            @if (!$payment->success)
                                <span style="color: darkred">Failed</span>
                            @else
                                <span style="color: green">Complete</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Amount Applied:</th>
                        <td>
                            &dollar;{{ number_format($payment->getAmountApplied(), 2) }}
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        @if(trim($payment->notes))
            <p class="mt-2 mb-2">
                <strong>Payment Notes: </strong> {{ $payment->notes }}
            </p>
        @endif

        @if($count = count($invoiceObjects))
            <h4 class="mt-4 mb-1">Invoice Payment Summary</h4>

            <div class="row">
                <div class="col-md-12">
                    <table class="table table-bordered items-table">
                        <thead>
                        <tr class="bg-info">
                            <th>Invoice Date</th>
                            <th>Invoice #</th>
                            <th>Client Name</th>
                            <th>Payer Name</th>
                            <th>Total Amount</th>
                            <th>Amount Applied</th>
                        </tr>
                        </thead>
                        <tbody>
                            @foreach($invoiceObjects as $invoiceObject)
                                <tr>
                                    <td class="text-nowrap">{{ $invoiceObject->invoice()->getDate() }}</td>
                                    <td>{{ $invoiceObject->invoice()->getName() }}</td>
                                    <td>{{ $invoiceObject->invoice()->client->name() }}</td>
                                    <td>{{ optional($invoiceObject->invoice()->getClientPayer())->name() }}</td>
                                    <td class="text-nowrap">{{ number_format($invoiceObject->invoice()->getAmount(), 2) }}</td>
                                    <td class="text-nowrap">{{ number_format($invoiceObject->amountApplied(), 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <h4 class="mt-4 mb-1">All Invoices</h4>

            @foreach($invoiceObjects as $invoiceObject)
                <h5>Invoice #{{ $invoiceObject->invoice()->getName() }}</h5>

                @include('invoices.partials.items_table', ['invoice' => $invoiceObject->invoice(), 'itemGroups' => $invoiceObject->itemGroups()])
            @endforeach
        @endif

        <div class="row">
            <div class="col text-center p-4">
                <h5>This is a statement. Your payment was processed on {{ $payment->created_at->format('m/d/Y') }} using your payment information on file.</h5>
            </div>
        </div>

        <div class="row">
            <div class="col">
                <p class="text-center">
                    (800) 930-0587<br>
                    allyms.com<br>
                    support@allyms.com
                </p>
                <p class="text-center"><em>Thank you for your business!</em></p>
            </div>
        </div>

        <div class="row">
            <div class="col">
                <div class="pull-right" style="margin-right: 1rem;">&copy; {{ \Carbon\Carbon::now()->year }} Ally</div>
            </div>
        </div>
    </div>
@endsection