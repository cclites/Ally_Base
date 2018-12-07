@extends('layouts.app')

@section('title', $client->name())

@section('avatar')
    <user-avatar src="{{ $client->avatar }}" title="{{ $client->name() }}" size="50"></user-avatar>
@endsection

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('business.clients.index') }}">Clients</a></li>
    <li class="breadcrumb-item active">{{ $client->name() }}</li>
@endsection

@section('content')
    <?php
        $defaultPaymentTypeMessage = "Active Payment Type: " . $client->getPaymentType() . " (" . round($client->getAllyPercentage() * 100, 2) . "% Processing Fee)";
        $backupPaymentTypeMessage = "Active Payment Type: " . $client->getPaymentType($client->backupPayment) . " (" . round($client->getAllyPercentage($client->backupPayment) * 100, 2) . "% Processing Fee)";
    ?>

    <!-- Nav tabs -->
    <ul class="nav nav-pills with-padding-bottom hidden-lg-down profile-tabs" role="tablist">
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
            <a class="nav-link" data-toggle="tab" href="#payment" role="tab">Payment Methods</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#caregivers" role="tab">Caregivers</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#care_plans" role="tab">Service Needs &amp; Goals</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#service_orders" role="tab">Service Orders</a>
        </li>
        @if($business->scheduling)
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#schedule" role="tab">Schedule</a>
            </li>
        @endif
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#client_notes" role="tab">Notes</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#documents" role="tab">Documents</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#client_payment_history" role="tab">Client Statements</a>
        </li>
        <li class="nav-item">
            <a data-toggle="tab" role="tab" href="#emergency_contacts" class="nav-link">Emergency Contacts</a>
        </li>
        <li class="nav-item">
            <a data-toggle="tab" role="tab" href="#ltci" class="nav-link">Insurance Data</a>
        </li>
        @if($client->client_type === 'medicaid')
            <li class="nav-item">
                <a data-toggle="tab" role="tab" href="#ltci" class="nav-link">Medicaid Data</a>
            </li>
        @endif
    </ul>

    <!-- Smaller device tabs -->
    <ul class="nav nav-pills with-padding-bottom hidden-xl-up profile-tabs" role="tablist">
        <li class="nav-item dropdown">
            <a class="nav-link active dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">Change Tab: <span class="tab-name">Profile</span></a>
            <div class="dropdown-menu">
                <a class="dropdown-item" data-toggle="tab" href="#profile" role="tab">Profile</a>
                <a class="dropdown-item" data-toggle="tab" href="#addresses" role="tab">Addresses</a>
                <a class="dropdown-item" data-toggle="tab" href="#phones" role="tab">Phone Numbers</a>
                <a class="dropdown-item" data-toggle="tab" href="#payment" role="tab">Payment Methods</a>
                <a class="dropdown-item" data-toggle="tab" href="#caregivers" role="tab">Caregivers</a>
                <a class="dropdown-item" data-toggle="tab" href="#care_plans" role="tab">Service Needs &amp; Goals</a>
                <a class="dropdown-item" data-toggle="tab" href="#service_orders" role="tab">Service Orders</a>
                <a class="dropdown-item" data-toggle="tab" href="#schedule" role="tab">Schedule</a>
                <a class="dropdown-item" data-toggle="tab" href="#client_notes" role="tab">Notes</a>
                <a class="dropdown-item" data-toggle="tab" href="#documents" role="tab">Documents</a>
                <a class="dropdown-item" data-toggle="tab" href="#client_payment_history" role="tab">Payment History</a>
                <a class="dropdown-item" data-toggle="tab" href="#emergency_contacts" role="tab">Emergency Contacts</a>
                <a class="dropdown-item" data-toggle="tab" href="#ltci" role="tab">Insurance Data</a>
            </div>
        </li>
    </ul>
    </ul>

    <!-- Tab panes -->
    <div class="tab-content">
        <div class="tab-pane active" id="profile" role="tabpanel">
            <div class="row">
                <div class="col-lg-12">
                    <client-edit :client="{{ $client }}" confirm-url="{{ route('reconfirm.encrypted_id', [$client->getEncryptedKey()]) }}" last-status-date="{{ $lastStatusDate }}"></client-edit>
                </div>
            </div>
        </div>
        <div class="tab-pane" id="addresses" role="tabpanel">
            <business-client-addresses-tab :addresses="{{ $client->addresses }}" client-id="{{ $client->id }}"></business-client-addresses-tab>
        </div>
        <div class="tab-pane" id="phones" role="tabpanel">
            <business-client-phone-numbers-tab :user="{{ $client }}"></business-client-phone-numbers-tab>
        </div>
        <div class="tab-pane" id="payment" role="tabpanel">
            <div class="row">
                <div class="col-lg-6 col-sm-12">
                    <payment-method title="Primary Payment Method"
                                    source="primary"
                                    :method="{{ $client->defaultPayment OR '{}' }}"
                                    :client="{{ $client }}"
                                    payment-type-message="{{ $defaultPaymentTypeMessage }}"
                                    role="{{ auth()->user()->role_type }}"
                                    :business="true">
                    </payment-method>
                </div>
                <div class="col-lg-6 col-sm-12">
                    <payment-method title="Backup Payment Method"
                                    source="backup"
                                    :method="{{ $client->backupPayment OR '{}' }}"
                                    :client="{{ $client }}"
                                    payment-type-message="{{ $backupPaymentTypeMessage }}"
                                    role="{{ auth()->user()->role_type }}"
                                    :business="true">
                    </payment-method>
                </div>
            </div>
        </div>
        <div class="tab-pane" id="caregivers" role="tabpanel">
            <business-client-caregivers :client="{{ $client }}"
                                        :ally-rate="{{ floatval($client->allyFee) }}"
                                        payment-type-message="{{ $defaultPaymentTypeMessage }}"
            ></business-client-caregivers>
        </div>
        <div class="tab-pane" id="care_plans" role="tabpanel">
            <business-client-care-plans-tab :client="{{ $client }}" :activities="{{ $business->allActivities() }}"></business-client-care-plans-tab>
            <business-client-goals :client="{{ $client }}" :goals="[]"></business-client-goals>
            <business-client-care-details
                :client="{{ $client }}" />
        </div>
        <div class="tab-pane" id="service_orders" role="tabpanel">
            <business-client-service-orders :client="{{ $client }}"></business-client-service-orders>
        </div>
        @if($business->scheduling)
            <div class="tab-pane" id="schedule" role="tabpanel">
                <business-schedule :client="{{ $client }}" :business="{{ activeBusiness() }}"></business-schedule>
            </div>
        @endif
        <div class="tab-pane" id="client_notes" role="tabpanel">
            <notes-tab :notes="{{ $client->notes }}" :business="{{ $business }}" :client="{{ $client }}"></notes-tab>
        </div>
        <div class="tab-pane" id="documents" role="tabpanel">
            <document-list
                :initial-documents="{{ $client->user->documents->toJson() }}"
                :user-id="{{ $client->user->id }}"
            ></document-list>
        </div>
        <div class="tab-pane" id="client_payment_history" role="tabpanel">
            <client-statements-tab :payments="{{ $client->payments }}"></client-statements-tab>
        </div>
        <div class="tab-pane" id="emergency_contacts" role="tabpanel">
            <emergency-contacts-tab :emergency-contacts="{{ $client->user->emergencyContacts }}"
                                    :user-id="{{ $client->id }}"></emergency-contacts-tab>
        </div>
        <div class="tab-pane" id="ltci">
            <client-ltc-insurance :client="{{ $client }}"></client-ltc-insurance>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Schedule fixes
        $('.profile-tabs a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            // Render calendar upon switching tabs
            if (e.target.hash === '#schedule') {
                $('#calendar').fullCalendar('render');
                $('#calendar').fullCalendar('refetchResources');
            }
        });

        // Javascript to enable link to tab
        var url = document.location.toString();
        if (url.match('#')) {
            $('.profile-tabs .nav-item a[href="#' +
                url.split('#')[1] + '"]').tab('show');
        }

        // Change hash for page-reload
        $('.profile-tabs .nav-item a').on('shown.bs.tab', function (e) {
            window.location.hash = e.target.hash;
            window.scrollTo(0,0);
        })
    </script>
@endpush
