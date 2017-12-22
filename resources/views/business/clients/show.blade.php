@extends('layouts.app')

@section('title', $client->name())

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
            <a class="nav-link" data-toggle="tab" href="#caregivers" role="tab">Caregivers</a>
        </li>
        <!-- <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#care-plan" role="tab">Care Plan</a>
        </li> -->
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#payment" role="tab">Payment Methods</a>
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
    </ul>

    <!-- Smaller device tabs -->
    <ul class="nav nav-pills with-padding-bottom hidden-xl-up" role="tablist">
        <li class="nav-item dropdown">
            <a class="nav-link active dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">Change Tab: <span class="tab-name">Profile</span></a>
            <div class="dropdown-menu">
                <a class="dropdown-item" data-toggle="tab" href="#profile" role="tab">Profile</a>
                <a class="dropdown-item" data-toggle="tab" href="#addresses" role="tab">Addresses</a>
                <a class="dropdown-item" data-toggle="tab" href="#phones" role="tab">Phone Numbers</a>
                <a class="dropdown-item" data-toggle="tab" href="#caregivers" role="tab">Caregivers</a>
                <a class="dropdown-item" data-toggle="tab" href="#payment" role="tab">Payment Methods</a>
                <a class="dropdown-item" data-toggle="tab" href="#service_orders" role="tab">Service Orders</a>
                <a class="dropdown-item" data-toggle="tab" href="#schedule" role="tab">Schedule</a>
                <a class="dropdown-item" data-toggle="tab" href="#client_notes" role="tab">Notes</a>
                <a class="dropdown-item" data-toggle="tab" href="#documents" role="tab">Documents</a>
                <a class="dropdown-item" data-toggle="tab" href="#client_payment_history" role="tab">Payment History</a>
                <a class="dropdown-item" data-toggle="tab" href="#emergency_contacts" role="tab">Emergency Contacts</a>
            </div>
        </li>
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
            {{--<div class="row">--}}
                {{--<div class="col-md-6 col-sm-12">--}}
                    {{--<user-address title="Service Address" type="evv" action="{{ route('business.clients.address', [$client->id, 'evv']) }}" :address="{{ $client->addresses->where('type', 'evv')->first() ?? '{}' }}"></user-address>--}}
                {{--</div>--}}
                {{--<div class="col-md-6 col-sm-12">--}}
                    {{--<user-address title="Billing Address" type="billing" action="{{ route('business.clients.address', [$client->id, 'billing']) }}" :address="{{ $client->addresses->where('type', 'billing')->first() ?? '{}' }}"></user-address>--}}
                {{--</div>--}}
            {{--</div>--}}
        </div>
        <div class="tab-pane" id="phones" role="tabpanel">
            <business-client-phone-numbers-tab :user="{{ $client }}"></business-client-phone-numbers-tab>
        </div>
        <div class="tab-pane" id="caregivers" role="tabpanel">
            <business-client-caregivers :client_id="{{ $client->id }}"
                                        :ally-fee="{{ floatval($client->allyFee) }}"
                                        payment-type-message="{{ $defaultPaymentTypeMessage }}"
            ></business-client-caregivers>
        </div>
        <div class="tab-pane" id="care-plan" role="tabpanel">
            <!-- Care Plan Placeholder -->
            <h4>Care Plan coming soon</h4>
            <p>This will be where you can set the activities and other care requirements for a client.</p>
        </div>
        <div class="tab-pane" id="payment" role="tabpanel">
            <div class="row">
                <div class="col-lg-6 col-sm-12">
                    <payment-method title="Primary Payment Method"
                                    source="primary"
                                    :method="{{ $client->defaultPayment OR '{}' }}"
                                    :client="{{ $client }}"
                                    payment-type-message="{{ $defaultPaymentTypeMessage }}"
                                    :business="true">
                    </payment-method>
                </div>
                <div class="col-lg-6 col-sm-12">
                    <payment-method title="Backup Payment Method"
                                    source="backup"
                                    :method="{{ $client->backupPayment OR '{}' }}"
                                    :client="{{ $client }}"
                                    payment-type-message="{{ $backupPaymentTypeMessage }}"
                                    :business="true">
                    </payment-method>
                </div>
            </div>
        </div>
        <div class="tab-pane" id="service_orders" role="tabpanel">
            <business-client-service-orders :client="{{ $client }}"></business-client-service-orders>
        </div>
        <div class="tab-pane" id="schedule" role="schedule">
            <client-schedule :client="{{ $client }}" :schedules="{{ $schedules }}"></client-schedule>
        </div>
        <div class="tab-pane" id="client_notes" role="tabpanel">
            <notes-tab :notes="{{ $client->notes }}"></notes-tab>
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
    </div>
@endsection

@push('scripts')
    <script>
        // Render Calendar inside Tab
        $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            $('#calendar').fullCalendar('render');
        });

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
