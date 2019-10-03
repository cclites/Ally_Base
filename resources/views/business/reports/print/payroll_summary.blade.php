@extends('layouts.print')

@section('title', "Payroll Summary")

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
        <div class="h4">Payroll Summary</div>
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