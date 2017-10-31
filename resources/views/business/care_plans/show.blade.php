@extends('layouts.app')

@section('title', $plan->name)

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('business.care_plans.index') }}">Care Plans</a></li>
    <li class="breadcrumb-item active">{{ $plan->name }}</li>
@endsection

@section('content')
    <care-plan-edit :plan="{{ $plan OR '{}' }}" :activities="{{ $activities OR '[]' }}"></care-plan-edit>
@endsection