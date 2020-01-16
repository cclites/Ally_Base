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
            <a data-toggle="tab" role="tab" href="#payers" class="nav-link">Payers</a>
        </li>
        <li class="nav-item">
            <a data-toggle="tab" role="tab" href="#rates" class="nav-link">Caregivers &amp; Rates</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#care_plans" role="tab">Service Needs &amp; Goals</a>
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
            <a class="nav-link" data-toggle="tab" href="#client_invoice_history" role="tab">Invoices</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#client_payment_history" role="tab">Payments</a>
        </li>
        <li class="nav-item">
            <a data-toggle="tab" role="tab" href="#contacts" class="nav-link">Contacts</a>
        </li>
        <li class="nav-item">
            <a data-toggle="tab" role="tab" href="#preferences" class="nav-link">Preferences</a>
        </li>
        <li class="nav-item">
            <a data-toggle="tab" role="tab" href="#insurance_service_auth" class="nav-link">Insurance & Service Auths</a>
        </li>
        <li class="nav-item">
            <a data-toggle="tab" role="tab" href="#misc" class="nav-link">Misc</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#notifications" role="tab">Notifications</a>
        </li>
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
                <a class="dropdown-item" data-toggle="tab" href="#payers" role="tab">Payers</a>
                <a class="dropdown-item" data-toggle="tab" href="#rates" role="tab">Caregivers &amp; Rates</a>
                <a class="dropdown-item" data-toggle="tab" href="#care_plans" role="tab">Service Needs &amp; Goals</a>
                <a class="dropdown-item" data-toggle="tab" href="#schedule" role="tab">Schedule</a>
                <a class="dropdown-item" data-toggle="tab" href="#client_notes" role="tab">Notes</a>
                <a class="dropdown-item" data-toggle="tab" href="#documents" role="tab">Documents</a>
                <a class="dropdown-item" data-toggle="tab" href="#client_invoice_history" role="tab">Invoices</a>
                <a class="dropdown-item" data-toggle="tab" href="#client_payment_history" role="tab">Payments</a>
                <a class="dropdown-item" data-toggle="tab" href="#contacts" role="tab">Contacts</a>
                <a class="dropdown-item" data-toggle="tab" href="#preferences" role="tab">Preferences</a>
                <a class="dropdown-item" data-toggle="tab" href="#insurance_service_auth" role="tab">Insurance & Service Auths</a>
                <a class="dropdown-item" data-toggle="tab" href="#misc" role="tab">Misc</a>
                <a class="dropdown-item" data-toggle="tab" href="#notifications" role="tab">Notifications</a>    
            </div>
        </li>
    </ul>

    <!-- Tab panes -->
    <div class="tab-content">
        <div class="tab-pane active" id="profile" role="tabpanel">
            <div class="row">
                <div class="col-lg-12">
                    <client-edit :client="{{ $client }}" :sales-people="{{ $salesPeople }}" last-status-date="{{ $lastStatusDate }}" :chain-client-type-settings="{{ $chainClientTypeSettings ? $chainClientTypeSettings : '{}' }}"></client-edit>
                </div>
            </div>
        </div>
        <div class="tab-pane" id="addresses" role="tabpanel">
            <business-client-addresses-tab :addresses="{{ $client->addresses }}" client-id="{{ $client->id }}" :addresses="{{ $client->addresses->where('type', 'billing')->first() ?? '{}' }}"></business-client-addresses-tab>
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
        <div class="tab-pane" id="payers" role="tabpanel">
            <client-payers-tab :client="{{ $client }}" :payers="{{ $client->payers }}" :payer-options="{{ $payers }}" />
        </div>
        <div class="tab-pane" id="preferences" role="tabpanel">
            <client-preferences-tab :client="{{ $client }}" />
        </div>
        <div class="tab-pane" id="rates" role="tabpanel">
            <!-- Includes caregivers -->
            <client-rates-tab :client="{{ $client }}"
                              :rates="{{ $client->rates }}"
                              :ally-rate-original="{{ floatval($client->allyFee) }}"
                              payment-type-message="{{ $defaultPaymentTypeMessage }}"
            />
        </div>
        <div class="tab-pane" id="care_plans" role="tabpanel">
            <business-client-care-plans-tab :client="{{ $client }}" :activities="{{ $business->allActivities() }}"></business-client-care-plans-tab>
            <business-client-goals :client="{{ $client }}" :goals="[]"></business-client-goals>
            <client-medication :client="{{ $client }}" :medications="{{ $client->medications }}"></client-medication>
            <b-card
                header="Client Narrative"
                header-text-variant="white"
                header-bg-variant="info"
                >
                    <client-narrative :client="{{ $client }}" mode="admin" />
            </b-card>

            <b-tabs pills content-class="mt-3">
              <b-tab title="Detailed Client Care Needs" active>
                  <business-client-care-details :client="{{ $client }}" />
              </b-tab>
              <b-tab title="Skilled Nursing POC">
                  <business-client-skilled-nursing-poc :client="{{ $client }}" />
              </b-tab>
            </b-tabs>
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
                :active="{{ $client->active }}"
                type="client"
            ></document-list>
        </div>
        <div class="tab-pane" id="client_invoice_history" role="tabpanel">
            <b-card title="Invoice History">
                <client-invoice-history :client="{{ $client }}" :invoices="{{ $invoices OR "[]" }}">
                    <template slot="actions" scope="data">
                        <a :href="'/business/client/invoices/' + data.item.id" class="btn btn-secondary" target="_blank">
                            <i class="fa fa-external-link"></i> View
                        </a>
                        <a :href="'/business/client/invoices/' + data.item.id + '/pdf'" class="btn btn-secondary">
                            <i class="fa fa-file-pdf-o"></i> Download
                        </a>
                    </template>
                </client-invoice-history>
            </b-card>
        </div>
        <div class="tab-pane" id="client_payment_history" role="tabpanel">
            <b-card title="Payment History">
                <client-payment-history :client="{{ $client }}">
                    <template slot="actions" scope="data">
                        <a :href="'/business/client/payments/' + data.item.id" class="btn btn-secondary" target="_blank">
                            <i class="fa fa-external-link"></i> View
                        </a>
                        <a :href="'/business/client/payments/' + data.item.id + '/pdf'" class="btn btn-secondary">
                            <i class="fa fa-file-pdf-o"></i> Download
                        </a>
                    </template>
                </client-payment-history>
            </b-card>
        </div>
        <div class="tab-pane" id="contacts" role="tabpanel">
            <client-contacts-tab :contacts="{{ $client->contacts }}" :client="{{ $client }}"></client-contacts-tab>
        </div>
        <div class="tab-pane" id="insurance_service_auth">
            <client-insurance-service-auth :client="{{ $client }}" :payers="{{ $payers }}" :services="{{ $services }}" :auths="{{ $auths }}"></client-insurance-service-auth>
        </div>
        <div class="tab-pane" id="misc">
            <client-misc-tab :client="{{ $client }}"></client-misc-tab>
        </div>
        <div class="tab-pane" id="notifications" role="tabpanel">
            <notification-preferences :admin="true" :user="{{ $client->user }}" :notifications="{{ $notifications }}"></notification-preferences>
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
            history.pushState({}, '', url.split('#')[0] + e.target.hash);
        })
    </script>
@endpush
