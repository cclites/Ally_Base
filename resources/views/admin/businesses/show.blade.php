@extends('layouts.app')

@section('title', $business->name)

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.businesses.index') }}">Businesses</a></li>
    <li class="breadcrumb-item active">{{ $business->name }}</li>
@endsection

@section('content')
    <business-edit :business="{{ $business OR '{}' }}"></business-edit>
@endsection