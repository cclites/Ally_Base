@extends('layouts.app')

@section('title', 'Profile')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
    <li class="breadcrumb-item active">Profile</li>
@endsection

@section('content')
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
                    <user-address title="Home Address" type="home" :address="{{ $user->addresses->where('type', 'home')->first() ?? '{}' }}"></user-address>
                </div>
            </div>
        </div>
        <div class="tab-pane" id="phones" role="tabpanel">
            <caregiver-phone-numbers-tab :phone-numbers="{{ $user->phoneNumbers }}"></caregiver-phone-numbers-tab>
            {{--@foreach ($user->phoneNumbers as $phone_number)--}}
                {{--@if ($loop->first)--}}
                    {{--<b-row>--}}
                {{--@endif--}}
                {{--<div class="col-lg-6 col-sm-12">--}}
                    {{--<phone-number title="{{ ucfirst($phone_number->type) }} Number"--}}
                                  {{--type="{{ $phone_number->type }}"--}}
                                  {{--:phone="{{ json_phone($user, $phone_number->type) }}">--}}
                    {{--</phone-number>--}}
                {{--</div>--}}
                {{--@if ($loop->last)--}}
                    {{--</b-row>--}}
                {{--@endif--}}
            {{--@endforeach--}}
        </div>
    </div>
@endsection
