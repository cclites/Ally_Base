@extends('layouts.app')

@section('title', 'Third Party Payer Report')

@section('content')
    <third-party-payer
            :payers = "{{ $payers }}"
            :caregivers = "{{ $caregivers }}"
            :clients = "{{ $clients }}">
    </third-party-payer>
@endsection