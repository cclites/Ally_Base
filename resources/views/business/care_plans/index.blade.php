@extends('layouts.app')

@section('title', 'Care Plan List')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item active">Care Plans</li>
@endsection

@section('content')
    <care-plan-list :plans="{{ $plans }}"></care-plan-list>
@endsection