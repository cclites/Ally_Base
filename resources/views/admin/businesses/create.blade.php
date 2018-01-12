@extends('layouts.app')

@section('title', 'New Provider')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.businesses.index') }}">Providers</a></li>
    <li class="breadcrumb-item active">Add a New Provider</li>
@endsection

@section('content')
    <business-create></business-create>
@endsection