<?php
/**
 * @var \App\Billing\Contracts\InvoiceInterface $invoice
 * @var \App\Contracts\ContactableInterface $sender
 * @var \App\Contracts\ContactableInterface $recipient
 * @var \Illuminate\Support\Collection $itemGroups
 * @var \Illuminate\Support\Collection $payments
 */
?>
@extends('layouts.print')

@section('title', 'Invoice #' . $invoice->getName())

@push('head')
    <style>
        body {
            color: #000;
        }

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
            background-color: #ccc;
            padding: 15px;
        }

        .logo img {
            max-height: 80px;
        }
    </style>
@endpush

@section('content')
    <div class="">
        <div class="row print-header">
            <div class="header-left">
                <div class="logo"><img src="{{ asset('/images/AllyLogo.png') }}" /></div>
                <div class="h4">{{ $sender->name() }}</div>
                <br>
                @include('invoices.partials.address', ['address' => $sender->getAddress(), 'phone' => $sender->getPhoneNumber()])
            </div>
            <div class="text-right header-right">
                <div class="h3">Invoice #{{ $invoice->getName() }}</div>
                <br>
                <table class="header-right-table">
                    <tr>
                        <td>Invoice Date</td>
                        <td>{{ $invoice->getDate() }}</td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <strong>{{ $recipient->name() }}</strong>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            @include('invoices.partials.address', ['address' => $recipient->getAddress(), 'phone' => $recipient->getPhoneNumber()])
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Invoice Status: </strong></td>
                        <td>
                            @if ($invoice->getAmountDue() > 0)
                                <span style="color: darkred">Unpaid</span>
                            @else
                                <span style="color: green">Paid</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        @yield('items')
        @yield('payments')

        <div class="row">
            <div class="footer-left">
                {{--<p>This is a statement. Your payment was processed on {{ $payment->created_at->setTimezone($timezone)->format('m/d/Y') }} using your payment information on file.</p>--}}
            </div>
            <div class="footer-right">
                <table class="table">
                    <tbody>
                    <tr>
                        <th>Total Amount:</th>
                        <td>
                            &dollar;{{ number_format($invoice->getAmount()) }}
                        </td>
                    </tr>
                    <tr>
                        <th>Amount Due:</th>
                        <td>
                            &dollar;{{ number_format($invoice->getAmountDue()) }}
                        </td>
                    </tr>
                    </tbody>
                </table>
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