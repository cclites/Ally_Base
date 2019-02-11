@extends('layouts.app')

@section('title', 'Invoice History')

@section('content')
    <b-card title="Invoice History">
        <client-invoice-history :client="{{ $client }}" :invoices="{{ $invoices }}"></client-invoice-history>
    </b-card>
@endsection