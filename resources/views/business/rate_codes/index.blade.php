@extends('layouts.app')

@section('title', 'Rate Codes')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item active">Rate Codes</li>
@endsection

@section('content')
    <business-rate-codes :rate-codes="{{ $rateCodes }}"></business-rate-codes>
@endsection
