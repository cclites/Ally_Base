@extends('layouts.print')

@section('title', "Invoice Summary By Salesperson")

@push('head')
    <style>

        .logo img {
            max-height: 80px;
        }

        table tr th{
            padding: 0 6px;
            width: 200px;
        }

        table tfoot tr td {
            padding-top: 40px;
        }

    </style>
@endpush

@section('content')
    @include('layouts.partials.print_logo')

    <div class="page" id="summary">
        <div class="h4">Invoice Summary By Salesperson</div>
        <div>
            <table>
                <thead>
                    <tr>
                        <th>SalesPerson</th>
                        <th>Client</th>
                        <th>Payer</th>
                        <th>Total Client Charges</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data as $item)
                        <tr>
                            <td>{{ $item['salesperson'] }}</td>
                            <td>{{ $item['client'] }}</td>
                            <td>{{ $item['payer'] }}</td>
                            <td>{{ money_format('%i',$item['amount']) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td><strong>For Dates: </strong>{{ $totals['start'] }} to {{ $totals['end'] }}</td>
                        <td><strong>For Salesperson: </strong> {{ $totals['salesperson'] }}</td>
                        <td><strong>For Client: </strong>{{ $totals['client'] }}</td>
                        <td><strong>Total Client Charges: </strong> ${{ money_format('%i', $totals['amount']) }}</td>
                    </tr>
                </tfoot>

            </table>
        </div>
    </div>

@endsection