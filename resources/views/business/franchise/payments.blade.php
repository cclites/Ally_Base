@extends('layouts.app')

@section('title', 'Franchises')
@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item active">Payments</li>
@endsection

@section('content')
    <business-franchise-payments>

    </business-franchise-payments>
@endsection
