@extends('layouts.app')

@section('title', 'Profile')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
    <li class="breadcrumb-item active">Profile</li>
@endsection

@section('content')
    <!-- Nav tabs -->
    <ul class="nav nav-pills with-padding-bottom hidden-sm-down" role="tablist">
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
            <a class="nav-link" data-toggle="tab" href="#bankaccount" role="tab">Direct Deposit</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#availability" role="tab">Availability</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#skills" role="tab">Skills</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#notifications" role="tab">Notifications</a>
        </li>
    </ul>

    <!-- Mobile nav tabs (drop down) -->
    <ul class="nav nav-pills with-padding-bottom hidden-md-up" role="tablist">
        <li class="nav-item dropdown">
            <a class="nav-link active dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">Change Tab: <span class="tab-name">Profile</span></a>
            <div class="dropdown-menu">
                <a class="dropdown-item" data-toggle="tab" href="#profile" role="tab">Profile</a>
                <a class="dropdown-item" data-toggle="tab" href="#addresses" role="tab">Addresses</a>
                <a class="dropdown-item" data-toggle="tab" href="#phones" role="tab">Phone Numbers</a>
                <a class="dropdown-item" data-toggle="tab" href="#bankaccount" role="tab">Direct Deposit</a>
                <a class="dropdown-item" data-toggle="tab" href="#availability" role="tab">Availability</a>
                <a class="dropdown-item" data-toggle="tab" href="#skills" role="tab">Skills</a>
                <a class="dropdown-item" data-toggle="tab" href="#notifications" role="tab">Notifications</a>
            </div>
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
        </div>
        <div class="tab-pane" id="bankaccount" role="tabpanel">
            <div class="row">
                <div class="col-lg-6 col-sm-12">
                    <div class="card">
                        <div class="card-header bg-info text-white">Bank Account</div>
                        <div class="card-body">
                            <bank-account-form :account="{{ $user->role->bankAccount OR '{}' }}" 
                                :submit-url="'{{ '/profile/bank-account' }}'" 
                                :readonly="authInactive" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="tab-pane" id="availability" role="tabpanel">
            <business-caregiver-availability-tab :caregiver="{{ $user->role }}" updated-by="{{ $user->role->availability->updatedByUser->name ?? '' }}"></business-caregiver-availability-tab>
        </div>
        <div class="tab-pane" id="skills" role="tabpanel">
            <business-caregiver-skills-tab :caregiver="{{ $user->role }}"></business-caregiver-skills-tab>
        </div>
        <div class="tab-pane" id="notifications" role="tabpanel">
            <div class="row">
                <div class="col-lg-12">
                    <notification-preferences :user="{{ $user }}" :notifications="{{ $notifications }}"></notification-preferences>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Javascript to enable link to tab
        var url = document.location.toString();
        if (url.match('#')) {
            $('.nav-item a[href="#' + url.split('#')[1] + '"]').tab('show');
        }

        // Change hash for page-reload
        $('.nav-item a').on('shown.bs.tab', function (e) {
            history.pushState({}, '', url.split('#')[0] + e.target.hash);
        })
    </script>
@endpush
