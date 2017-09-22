@extends('layouts.app')

@section('title', 'Profile')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
    <li class="breadcrumb-item active">Profile</li>
@endsection

@section('content')
    {{--<div class="row">--}}
        {{--<div class="col-12 text-right">--}}
            {{--<a href="#" class="btn btn-info">Addresses</a>--}}
            {{--<a href="#" class="btn btn-dark">Phone Numbers</a>--}}
            {{--<a href="#" class="btn btn-primary">Payment Info</a>--}}
        {{--</div>--}}
    {{--</div>--}}
    {{--<client-profile :user="{{ Auth::user() }}" :client="{{ Auth::user()->role }}"></client-profile>--}}

    <!-- Nav tabs -->
    <ul class="nav nav-pills with-padding-bottom" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" data-toggle="tab" href="#profile" role="tab">Profile</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#addresses" role="tab">Addresses</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#phones" role="tab">Phone Numbers</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#payment" role="tab">Payment Info</a>
        </li>
    </ul>

    <!-- Tab panes -->
    <div class="tab-content">
        <div class="tab-pane active" id="profile" role="tabpanel">
            <div class="row">
                <div class="col-lg-12">
                    <client-profile :user="{{ $user }}" :client="{{ $user->role }}"></client-profile>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <change-password></change-password>
                </div>
            </div>
        </div>
        <div class="tab-pane" id="addresses" role="tabpanel">
            <div class="row">
                <div class="col-md-6 col-sm-12">
                    <user-address title="Service Address" type="evv" :address="{{ $user->addresses->where('type', 'evv')->first() ?? '{}' }}"></user-address>
                </div>
                <div class="col-md-6 col-sm-12">
                    <user-address title="Billing Address" type="billing" :address="{{ $user->addresses->where('type', 'billing')->first() ?? '{}' }}"></user-address>
                </div>
            </div>
        </div>
        <div class="tab-pane" id="phones" role="tabpanel">

        </div>
        <div class="tab-pane" id="payment" role="tabpanel">

        </div>
    </div>
@endsection