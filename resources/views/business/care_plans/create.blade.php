@extends('layouts.app')

@section('title', 'New Care Plan')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('business.care_plans.index') }}">Care Plans</a></li>
    <li class="breadcrumb-item active">New Care Plan</li>
@endsection

@section('content')
    <care-plan-edit :activities="{{ $activities OR '[]' }}"></care-plan-edit>
@endsection