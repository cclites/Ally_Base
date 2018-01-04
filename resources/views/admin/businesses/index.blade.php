@extends('layouts.app')

@section('title', 'Provider List')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item active">Providers</li>
@endsection

@section('content')
    <business-list :businesses="{{ $businesses OR '[]' }}"></business-list>
@endsection