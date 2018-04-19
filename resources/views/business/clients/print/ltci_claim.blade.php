@extends('layouts.print')

@section('title', 'LTC Insurance Claim - Print')

@section('content')
    <style>
        .report {
            margin: 1rem;
        }
    </style>
    <div class="container-fluid report">
        <div class="row" style="margin-bottom: 2rem;">
            <div class="col">
                <div>
                    <div class="h4" style="margin-bottom: .25rem;">
                        {{ $client->ltci_name }}<br>
                        {{ $client->ltci_address }} {{ $client->ltci_city }}, {{ $client->ltci_state }}
                        {{ $client->ltci_zip }}
                    </div>
                </div>
                <div class="text-center h4">
                    Policy #: {{ $client->ltci_policy }}<br>
                    Claim #: {{ $client->ltci_claim }}
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col" @if(data_get($data, 'export_type') != 'pdf') style="margin: 0 10%" @endif>
                <div class="row">
                    <div class="col">
                        <div>
                            <div style="float: left; margin-left: 2rem;">
                                <b>Client Name:</b><br>{{ $client->name }}
                            </div>
                            <div style="float: right; margin-right: 2rem;">
                                <b>Client Address:</b><br>
                                @if ($client->addresses()->first())
                                    {{ $client->addresses()->first()->address1 }}
                                    @if ($client->addresses()->first()->address2)
                                        {{ $client->addresses()->first()->address2 }}
                                    @endif
                                    <br>
                                    {{ $client->addresses()->first()->city }},
                                    {{ $client->addresses()->first()->state }}
                                    {{ $client->addresses()->first()->zip }}
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <table class="table" style="margin-top: 1rem;">
                    <tbody>
                        <tr>
                            <th>Date</th>
                            <th>Hours</th>
                            <th>Rate</th>
                            <th>Amount</th>
                        </tr>
                        @foreach($summary as $item)
                        <tr>
                            <td>
                                {{ Carbon\Carbon::parse(data_get($item, 'date'))->format('m/d/Y') }}
                            </td>
                            <td>
                                {{ data_get($item, 'hours') }}
                            </td>
                            <td>
                                &dollar;{{ number_format(data_get($item, 'hourly_total'), 2) }}
                            </td>
                            <td>
                                &dollar;{{ number_format(data_get($item, 'total'), 2) }}
                            </td>
                        </tr>
                        @endforeach
                        <tr>
                            <td></td>
                            <td></td>
                            <td class="text-right">
                                <b>Total:</b>
                            </td>
                            <td>
                                &dollar;{{ number_format($summary->sum('total'), 2) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @if (data_get($data, 'timesheets'))
        <div>
            @include('business.reports.print.timesheets_contents')
        </div>
    @endif
@endsection