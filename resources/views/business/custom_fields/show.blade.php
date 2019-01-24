@extends('layouts.app')

@php($action = isset($field) ? 'Edit' : 'Create')
@section('title', $action . ' custom field')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('business.settings.index') }}">Settings</a></li>
    <li class="breadcrumb-item active">{{ $action .' custom field' }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <custom-field-edit :field="{{ $field ?? 'null' }}"></custom-field-edit>
        </div>
    </div>
@endsection
