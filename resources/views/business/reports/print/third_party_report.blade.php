@extends('layouts.print')

@section('title')
    Third Party Payer Report
@endsection

@push('head')
@endpush

@section('content')
    <table class="table">
        <thead>
        <tr>
            <th>Client</th>
            <th>Invoice #</th>
            <th>HIC #</th>
            <th>Client DOB</th>
            <th>Diagnosis Code</th>
            <th>Caregiver</th>
            <th>Payer</th>
            <th>Service Code & Type</th>
            <th>Authorization #</th>
            <th>Date</th>
            <th>Start</th>
            <th>End</th>
            <th>Units</th>
            <th>Hours</th>
            <th>Cost/Hour</th>
            <th>EVV</th>
            <th>Total Billable</th>
        </tr>
        </thead>
        <tbody>
        {{ $timezone }}
        @foreach($data as $item)
        <tr>
            <td>{{ $item['client_name'] }}</td>
            <td>{{ $item['invoice_name'] }}</td>
            <td>{{ $item['hic'] }}</td>
            <td>{{ $item['dob'] }}</td>
            <td>{{ $item['code'] }}</td>
            <td>{{ $item['caregiver'] }}</td>
            <td>{{ $item['payer'] }}</td>
            <td>{{ $item['service'] }}</td>
            <td>{{ $item['service_auth'] }}</td>
            <td>{{ \Carbon\Carbon::parse($item['date'])->format('m/d/Y') }}</td>
            <td>{{ \Carbon\Carbon::parse($item['start'])->setTimezone($timezone)->format('g:i A') }}</td>
            <td>{{ \Carbon\Carbon::parse($item['end'])->setTimezone($timezone)->format('g:i A') }}</td>
            <td>{{ $item['units'] }}</td>
            <td>{{ $item['hours'] }}</td>
            <td>{{ $item['rate'] }}</td>
            <td>{{ $item['evv'] }}</td>
            <td>{{ $item['billable'] }}</td>
        </tr>
        @endforeach
        </tbody>
    </table>
@endsection