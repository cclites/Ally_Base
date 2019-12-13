@extends('layouts.app')

@section('title', $chain->name)

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.businesses.chains') }}">Business Chains</a></li>
    <li class="breadcrumb-item active">{{ $chain->name }}</li>
@endsection

@section('content')
    <!-- Nav tabs -->
    <ul class="nav nav-pills with-padding-bottom hidden-lg-down" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" data-toggle="tab" href="#settings" role="tab">Settings</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#users" role="tab">Users</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#chain_1099_settings" role="tab">1099 Settings</a>
        </li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane active" id="settings" role="tabpanel">
            <div class="row">
                <div class="col-lg-12">
                    <business-chain-edit :chain="{{ $chain OR '{}' }}"></business-chain-edit>
                </div>
            </div>
        </div>

        <div class="tab-pane" id="users" role="tabpanel">
            <div class="row">
                <div class="col-lg-12">
                    <business-office-user-list :chain="{{ $chain }}" :businesses="{{ $chain->businesses OR '[]' }}"></business-office-user-list>
                </div>
            </div>
        </div>

        <div class="tab-pane" id="chain_1099_settings" role="tabpanel">
            <chain-1099-settings :chain="{{ $chain OR '{}' }}"></chain-1099-settings>
        </div>
    </div>
@endsection