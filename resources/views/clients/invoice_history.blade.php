@extends('layouts.app')

@section('title', 'Invoice History')

@section('content')
    <client-invoice-history :client="{{ $client }}" :invoices="{{ $invoices }}"></client-invoice-history>
@endsection