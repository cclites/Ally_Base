@extends('layouts.print')

@section('title', "Payment Summary By Private Payer")

@push('head')
    <style>
        .logo img {
            max-height: 80px;
        }

        table tr td{
            padding: 0 6px;
            width: 200px;
        }

        table tfoot tr td{
            padding-top: 40px;
        }

    </style>
@endpush

@section('content')
    @include('layouts.partials.print_logo')

    <div class="page" id="summary">
        <div class="h4">Payment Summary By Private Payer</div>
        <div>
            <table>
                <thead>
                <tr>
                    <th>Client</th>
                    <th style="width: 200px;">Invoice Date</th>
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