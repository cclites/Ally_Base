@extends('layouts.print')

@section('title', 'Payment Details - Print')

@push('head')
    <style>
        .header-left,
        .footer-left {
            float: left;
            width: 75%;
            padding-left: 0;
        }

        .footer-left {
            padding-left: 1rem;
        }

        .header-right,
        .footer-right {
            float: left;
            width: 25%;
            padding-right: 0;
        }

        .footer-right {
            padding-right: 1.2rem;
        }

        .header-right table tr td {
            padding-left: .5rem;
        }

        .print-header {
            margin: 0;
            background-color: #ccc;
            padding: 15px;
        }

        .logo img {
            max-height: 80px;
        }

        .row{
            padding-left: 20px;
            padding-right: 20px;
        }
    </style>
@endpush

@section('content')
    <div>
        <div class="row print-header">
            <div class="header-left">
                <div class="logo"><img src="{{ asset('/images/AllyLogo-new-light.png') }}" /></div>
            </div>
            <div class="text-right header-right">
            </div>
        </div>

        <div class="row">
            <strong>Deactivation Reason:</strong> {{ $caregiver->deactivationReason["name"] }}
                <br>
            <strong>Deactivation Note:</strong> {{ $caregiver->deactivation_note }}
                <br>
            <strong>Date: </strong> {{ $caregiver->in_active_at->format('m-d-Y') }}
                <br>
            <strong>By:</strong>  {{ $deactivatedBy }}
                <br>
        </div>

        <div class="row">
            <div class="col">
                <div class="pull-right">&copy; {{ \Carbon\Carbon::now()->year }} Ally</div>
            </div>
        </div>
    </div>
@endsection
