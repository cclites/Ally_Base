@extends('layouts.app')

@section('title', 'Apply Payment')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item">Accounting</li>
    <li class="breadcrumb-item active">Apply Payment</li>
@endsection

@section('content')
    <business-apply-payment :clients="{{ $clients }}" />
@endsection
