@extends('layouts.print')

@section('title', "Payroll Summary")

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

        table thead tr th{
            padding-bottom: 12px;
        }

        table tfoot tr td{
            padding-top: 40px;
        }

        table tbody tr td{
            padding-right: 20px;
        }

        table{
            margin-top: 20px;
        }

    </style>
@endpush

@section('content')

    <div class="page" id="summary">
        <div class="row print-header">
            <div class="header-left">
                <div class="logo"><img src="{{ asset('/images/AllyLogo.png') }}" /></div>
                <div class="h4">Payroll Summary</div>
            </div>
        </div>
        <div>
            <table>
                <thead>
                <tr>
                    <th><strong>Caregiver</strong></th>
                    <th><strong>Amount</strong></th>
                </tr>
                </thead>
                <tbody>
                @foreach($data as $item)
                    <tr>
                        <td>{{ $item['caregiver'] }}</td>
                        <td class="text-right">${{ money_format('%i',$item['amount']) }}</td>
                    </tr>
                @endforeach
                </tbody>
                <tfoot>
                <tr>
                    <td><strong>For Client Types: </strong>{{ $totals['type'] }}</td>
                    <td><strong>Total Payroll: </strong> ${{ money_format('%i', $totals['amount']) }}</td>
                </tr>
                </tfoot>

            </table>
        </div>
    </div>

@endsection