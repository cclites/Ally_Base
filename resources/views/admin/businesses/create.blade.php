@extends('layouts.app')

@section('title', 'New Business')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.businesses.index') }}">Businesses</a></li>
    <li class="breadcrumb-item active">Add a New Business</li>
@endsection

@section('content')
    <business-create></business-create>
@endsection