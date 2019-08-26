@extends('layouts.print')

@section('title', "Payment Summary By Private Payer")

@push('head')
    <style>
        .col-sm-6 {
            float: left;
            width: 50%;
        }

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

        .report-table {
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

    <div class="page" id="summary">
        <div class="row print-header">
            <div class="header-left">
                <div class="logo"><img src="{{ asset('/images/AllyLogo.png') }}" /></div>
                <div class="h4">Payment Summary By Private Payer</div>
            </div>
        </div>
        <div>
            <table>
                <thead>
                <tr>
                    <th>Client</th>
                    <th>Invoice Date</th>
                    <th>Invoice</th>
                    <th>Total Invoiced Amount</th>
                </tr>
                </thead>
                <tbody>
                @foreach($data as $item)
                    <tr>
                        <td>{{ $item['client_name'] }}</td>
                        <td>{{ $item['date'] }}</td>
                        <td>{{ $item['invoice'] }}</td>
                        <td>${{ money_format('%i',$item['amount']) }}</td>
                    </tr>
                @endforeach
                </tbody>
                <tfoot>
                <tr>
                    <td><strong>For Client: </strong>{{ $totals['client_name'] }}</td>
                    <td><strong>For Locations: </strong>{{ $totals['location'] }}</td>
                    <td>&nbsp;</td>
                    <td><strong>Total Invoiced Amount: </strong> ${{ money_format('%i',$totals['total']) }}</td>
                </tr>
                </tfoot>

            </table>
        </div>
    </div>

@endsection