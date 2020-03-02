@extends('layouts.print')

@section('title', 'Deactivation Summary')

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

    <div class="h4">Caregiver Deactivation Summary: {{ $caregiver->name }}</div>

    <div>
        <div class="row">
            <strong>Deactivation Reason:</strong> {{ $caregiver->deactivationReason["name"] ?? 'unspecified' }}
                <br>
            <strong>Deactivation Note:</strong> {{ $caregiver->deactivation_note ?? 'n/a' }}
                <br>
            <strong>Date: </strong> {{ \Carbon\Carbon::parse( $caregiver->in_active_at )->format('m-d-Y') }}
                <br>
            <strong>By:</strong>  {{ $deactivatedBy ?? 'n/a' }}
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
