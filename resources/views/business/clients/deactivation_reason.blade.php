@extends('layouts.print')

@section('title', 'Discharge Summary')

@push('head')
    <style>

        .logo img {
            max-height: 80px;
        }

        .row{
            padding: 0 20px;
        }
    </style>
@endpush

@section('content')
    @include('layouts.partials.print_logo')

    <div class="h4">Client Discharge Summary: {{ $client->name }}</div>

    <div>
        <div class="row">
            <strong>Deactivation Reason:</strong> {{ $client->deactivationReason[ "name" ] ?? 'unspecified' }}
                <br>
            <strong>Discharge Reason:</strong> {{ $client->discharge_reason ?? 'n/a' }}
                <br>
            <strong>Discharge Condition:</strong> {{ $client->discharge_condition ?? 'n/a' }}
                <br>
            <strong>Discharge Goals Evaluation:</strong> {{ $client->discharge_goals_eval ?? 'n/a' }}
                <br>
            <strong>Discharge Disposition:</strong> {{ $client->discharge_disposition ?? 'n/a' }}
                <br>
            <strong>Date: </strong> {{ \Carbon\Carbon::parse( $client->in_active_at, auth()->user()->getTimezone() )->format( 'm-d-Y' ) }}
                <br>
            <strong>By:</strong> {{ $deactivatedBy }}
                <br>
            <strong>Start of Care: </strong> {{ empty($client->service_start_date) ? 'Unknown' : \Carbon\Carbon::parse( $client->service_start_date )->format( 'm-d-Y' ) }}
                <br>
            <strong>Total Lifetime Hours:</strong> {{ $totalLifetimeHours }}
                <br>
            <strong>Total Lifetime Shifts:</strong> {{ $totalLifetimeShifts }}
                <br>
        </div>

        <div class="row">
            <div class="col">
                <div class="pull-right">&copy; {{ \Carbon\Carbon::now()->year }} Ally</div>
            </div>
        </div>
    </div>
@endsection
