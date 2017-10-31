@extends('layouts.app')

@section('title', 'Business List')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item active">Businesses</li>
@endsection

@section('content')
    <business-list :businesses="{{ $businesses OR '[]' }}"></business-list>
@endsection