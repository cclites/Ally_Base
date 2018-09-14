@extends('layouts.app')

@section('title', 'QuickBooks API')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item active">QuickBooks API</li>
@endsection

@section('content')
    @php $connect = session('connect') ?? '{}' @endphp
    <quickbooks-api
        :connect="{{ $connect }}"
        :authorization="{{ $authorization }}"
        :invoices="{{ $invoices }}"
        :customers="{{ $customers }}">
    </quickbooks-api>
@endsection