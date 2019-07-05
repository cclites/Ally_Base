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
            color: #000!important;
            font-size: 1.4rem;
            font-weight: 500;
        }
        small { font-weight: 500 }

        .header-left,
        .footer-left {
            float: left;
            width: 55%;
            padding-left: 0;
        }

        .header-right,
        .footer-right {
            float: left;
            width: 45%;
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

        .bg-danger {
            background-color: #808080!important;
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

        .clear {
            clear: both;
        }
    </style>
@endpush

@section('content')
    <div class="">
        <div class="row print-header">
            <div class="header-left">
                <div class="logo"><img src="{{ asset('/images/AllyLogo-new-light.png') }}" /></div>
                @if($sender->name())
                    <div class="h4">Associated Home Care Company: {{ $sender->name() }}</div>
                    <br>
                    <div class="sender-address">
                        @include('invoices.partials.address', ['address' => $sender->getAddress(), 'phone' => $sender->getPhoneNumber()])
                    </div>
                @endif
            </div>
            <div class="text-right header-right">
                <div class="h1">Invoice #{{ $invoice->getName() }}</div>
                <br>
                <table class="header-right-table">
                    <tr>
                        <td>Invoice Date: </td>
                        <td>{{ $invoice->getDate() }}</td>
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
                    @if (! $subject->name())
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
                        @if( filled($recipient->date_of_birth))
                            <tr>
                                <td colspan="2">
                                   {{
                                        $recipient->date_of_birth
                                   }}
                                </td>
                            </tr>
                        @endif
                        @if( filled($recipient->hic))
                            <tr>
                                <td colspan="2">
                                    {{ $recipient->hic }}
                                </td>
                            </tr>
                        @endif
                    @endif
                </table>
            </div>
        </div>

        @if ($subject->name())
            <div class="row print-header">
                <div style="width: 50%; float:left">
                    <table class="" style="margin-left: 3rem; margin: auto">
                        <tr>
                            <td colspan="2">
                                <strong>Bill To:</strong>
                            </td>
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
                        @if( filled($subject->date_of_birth))
                            <tr>
                                <td colspan="2">
                                    <strong>{{ $subject->date_of_birth }}</strong>
                                </td>
                            </tr>
                        @endif
                        @if( filled($subject->user->hic))
                            <tr>
                                <td colspan="2">
                                    <strong>{{ $subject->user->hic }}</strong>
                                </td>
                            </tr>
                        @endif
                    </table>
                </div>
                <div style="width: 50%; float:right">
                    <table class="" style="margin-right: 3rem; margin: auto">
                        <tr>
                            <td colspan="2">
                                <strong>Client:</strong>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <strong>{{ $subject->name() }}</strong>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                @include('invoices.partials.address', ['address' => $subject->getAddress(), 'phone' => $subject->getPhoneNumber()])
                            </td>
                        </tr>
                        @if( filled($subject->user->date_of_birth))
                        <tr>
                            <td colspan="2">
                                <strong>{{ $subject->user->date_of_birth }}</strong>
                            </td>
                        </tr>
                        @endif
                        @if( filled($subject->user->hic))
                        <tr>
                            <td colspan="2">
                                <strong>{{ $subject->user->hic }}</strong>
                            </td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>
        @endif

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
                        <th>Invoiced Amount:</th>
                        <td>
                            &dollar;{{ number_format($invoice->getAmount(), 2) }}
                        </td>
                    </tr>
                    <tr>
                        <th>Amount Due:</th>
                        <td>
                            &dollar;{{ number_format($invoice->getAmountDue(), 2) }}
                        </td>
                    </tr>
                    @if ($invoice->getAmountDue() <= 0)
                    <tr>
                        <td colspan="2">
                            <div class="h2">NOTHING DUE - THIS INVOICE HAS BEEN PAID</div>
                        </td>
                    </tr>
                    @endif
                    </tbody>
                </table>
            </div>
        </div>

        <div class="row mt-5">
            <div class="col">
                @if (! empty($sender->getPhoneNumber()))
                <p class="text-center"><em>For questions regarding hours and rates: {{ $sender->getPhoneNumber()->number() }}</em></p>
                @endif
                <p class="text-center">
                    For questions regarding payments: support@allyms.com - (800) 930-0587
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