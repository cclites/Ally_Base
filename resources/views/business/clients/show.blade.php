@extends('layouts.app')

@section('title', $client->name())

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('business.clients.index') }}">Clients</a></li>
    <li class="breadcrumb-item active">{{ $client->name() }}</li>
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
            <a class="nav-link" data-toggle="tab" href="#schedule" role="tab">Schedule</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#documents" role="tab">Documents</a>
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
            <div class="row">
                <div class="col-md-6 col-sm-12">
                    <user-address title="Service Address" type="evv" action="{{ route('business.clients.address', [$client->id, 'evv']) }}" :address="{{ $client->addresses->where('type', 'evv')->first() ?? '{}' }}"></user-address>
                </div>
                <div class="col-md-6 col-sm-12">
                    <user-address title="Billing Address" type="billing" action="{{ route('business.clients.address', [$client->id, 'billing']) }}" :address="{{ $client->addresses->where('type', 'billing')->first() ?? '{}' }}"></user-address>
                </div>
            </div>
        </div>
        <div class="tab-pane" id="phones" role="tabpanel">
            <div class="row">
                <div class="col-12 col-lg-6 col-xlg-4">
                    <phone-number title="Service Number" type="evv" action="{{ route('business.clients.phone', [$client->id, 'evv']) }}" :phone="{{ json_phone($client->user, 'evv') }}"></phone-number>
                </div>
                <div class="col-12 col-lg-6 col-xlg-4">
                    <phone-number title="Billing Number" type="billing" action="{{ route('business.clients.phone', [$client->id, 'billing']) }}" :phone="{{ json_phone($client->user, 'billing') }}"></phone-number>
                </div>
                <div class="col-12 col-lg-6 col-xlg-4">
                    <phone-number title="Home Number" type="home" action="{{ route('business.clients.phone', [$client->id, 'home']) }}" :phone="{{ json_phone($client->user, 'home') }}"></phone-number>
                </div>
            </div>
        </div>
        <div class="tab-pane" id="caregivers" role="tabpanel">
            <!-- Care Plan Placeholder -->
            <client-caregivers :list="{{ $caregivers }}" :client_id="{{ $client->id }}"></client-caregivers>
        </div>
        <div class="tab-pane" id="care-plan" role="tabpanel">
            <!-- Care Plan Placeholder -->
            <h4>Care Plan coming soon</h4>
            <p>This will be where you can set the activities and other care requirements for a client.</p>
        </div>
        <div class="tab-pane" id="payment" role="tabpanel">
            <div class="row">
                <div class="col-lg-6 col-sm-12">
                    <payment-method title="Primary Payment Method" source="primary" :method="{{ $client->defaultPayment OR '{}' }}" :client="{{ $client }}" />
                </div>
                <div class="col-lg-6 col-sm-12">
                    <payment-method title="Backup Payment Method" source="backup" :method="{{ $client->backupPayment OR '{}' }}" :client="{{ $client }}" />
                </div>
            </div>
        </div>
        <div class="tab-pane" id="schedule" role="schedule">
            <client-schedule :client="{{ $client }}" :schedules="{{ $schedules }}"></client-schedule>
        </div>
        <div class="tab-pane" id="documents" role="tabpanel">
            <document-list
                :initial-documents="{{ $client->user->documents->toJson() }}"
                :user-id="{{ $client->user->id }}"
            ></document-list>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            $('#calendar').fullCalendar('render');
        })
    </script>
@endpush
