@extends('layouts.print')

@section('title', "Invoice Summary By County")

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

    <div class="page" id="summary">
        <div class="h4">Invoice Summary By Salesperson</div>
        <div>
            <table>
                <thead>
                <tr>
                    <th>County</th>
                    <th>Total Hours</th>
                    <th>Total Client Charges</th>
                </tr>
                </thead>
                <tbody>
                @foreach($data as $item)
                    <tr>
                        <td>{{ $item['county'] }}</td>
                        <td>{{ $item['hours'] }}</td>
                        <td>${{ money_format('%i',$item['amount']) }}</td>
                    </tr>
                @endforeach
                </tbody>
                <tfoot>
                <tr>
                    <td><strong>For Dates: </strong>{{ $totals['start'] }} to {{ $totals['end'] }}</td>
                    <td><strong>For Location: </strong> {{ $totals['location'] }}</td>
                    <td><strong>Total Client Charges: </strong> ${{ money_format('%i', $totals['amount']) }}</td>
                </tr>
                </tfoot>

            </table>
        </div>
    </div>

@endsection