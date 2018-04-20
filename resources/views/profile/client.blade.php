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
    <ul class="nav nav-pills with-padding-bottom hidden-lg-down" role="tablist">
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
        <li class="nav-item">
            <a data-toggle="tab" role="tab" href="#emergency_contacts" class="nav-link">Emergency Contacts</a>
        </li>
    </ul>

    <!-- Smaller device tabs -->
    <ul class="nav nav-pills with-padding-bottom hidden-xl-up" role="tablist">
        <li class="nav-item dropdown">
            <a class="nav-link active dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">Change Tab: <span class="tab-name">Profile</span></a>
            <div class="dropdown-menu">
                <a class="dropdown-item" data-toggle="tab" href="#profile" role="tab">Profile</a>
                <a class="dropdown-item" data-toggle="tab" href="#addresses" role="tab">Addresses</a>
                <a class="dropdown-item" data-toggle="tab" href="#phones" role="tab">Phone Numbers</a>
                <a class="dropdown-item" data-toggle="tab" href="#payment" role="tab">Payment Info</a>
                <a class="dropdown-item" data-toggle="tab" href="#emergency_contacts" role="tab">Emergency Contacts</a>
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
                    <user-address title="Service Address" type="evv" :address="{{ $user->addresses->where('type', 'evv')->first() ?? '{}' }}"></user-address>
                </div>
                <div class="col-md-6 col-sm-12">
                    <user-address title="Billing Address" type="billing" :address="{{ $user->addresses->where('type', 'billing')->first() ?? '{}' }}"></user-address>
                </div>
            </div>
        </div>
        <div class="tab-pane" id="phones" role="tabpanel">
            <client-phone-numbers-tab :phone-numbers="{{ $user->phoneNumbers }}"></client-phone-numbers-tab>
        </div>
        <div class="tab-pane" id="payment" role="tabpanel">
            <div class="row">
                <div class="col-lg-6 col-sm-12">
                    <payment-method title="Primary Payment Method"
                                    source="primary"
                                    :method="{{ $user->role->defaultPayment OR '{}' }}"
                                    :client="{{ $user->role }}"
                                    payment-type-message="{{ $payment_type_message['default'] }}"
                                    role="{{ auth()->user()->role_type }}"
                                    :business="false">
                    </payment-method>
                </div>
                <div class="col-lg-6 col-sm-12">
                    <payment-method title="Backup Payment Method"
                                    source="backup"
                                    :method="{{ $user->role->backupPayment OR '{}' }}"
                                    :client="{{ $user->role }}"
                                    payment-type-message="{{ $payment_type_message['backup'] }}"
                                    role="{{ auth()->user()->role_type }}"
                                    :business="false">
                    </payment-method>
                </div>
            </div>
        </div>
        <div class="tab-pane" id="emergency_contacts" role="tabpanel">
            <emergency-contacts-tab :emergency-contacts="{{ $user->emergencyContacts }}" :user-id="{{ $user->id }}"></emergency-contacts-tab>
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
            window.location.hash = e.target.hash;
            window.scrollTo(0,0);
        })
    </script>
@endpush