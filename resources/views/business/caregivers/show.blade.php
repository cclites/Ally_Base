@extends('layouts.app')

@section('title', $caregiver->name())

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('business.caregivers.index') }}">Caregivers</a></li>
    <li class="breadcrumb-item active">{{ $caregiver->name() }}</li>
@endsection

@section('content')
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
            <a class="nav-link" data-toggle="tab" href="#bankaccount" role="tab">Direct Deposit</a>
        </li>
        @if ($business->scheduling)
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#schedule" role="tab">Schedule</a>
            </li>
        @endif
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#caregiver_notes" role="tab">Notes</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#documents" role="tab">Documents</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#licenses" role="tab">Certifications</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#availability" role="tab">Availability</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#preferences" role="tab">Preferences</a>
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
                <a class="dropdown-item" data-toggle="tab" href="#bankaccount" role="tab">Direct Deposit</a>
                <a class="dropdown-item" data-toggle="tab" href="#schedule" role="tab">Schedule</a>
                <a class="dropdown-item" data-toggle="tab" href="#caregiver_notes" role="tab">Notes</a>
                <a class="dropdown-item" data-toggle="tab" href="#documents" role="tab">Documents</a>
                <a class="dropdown-item" data-toggle="tab" href="#licenses" role="tab">Certifications</a>
                <a class="dropdown-item" data-toggle="tab" href="#availability" role="tab">Availability</a>
                <a class="dropdown-item" data-toggle="tab" href="#preferences" role="tab">Preferences</a>
                <a class="dropdown-item" data-toggle="tab" href="#emergency_contacts" role="tab">Emergency Contacts</a>
            </div>
        </li>
    </ul>

    <!-- Tab panes -->
    <div class="tab-content">
        <div class="tab-pane active" id="profile" role="tabpanel">
            <div class="row">
                <div class="col-lg-12">
                    <caregiver-edit :caregiver="{{ $caregiver }}"></caregiver-edit>
                </div>
            </div>
        </div>
        <div class="tab-pane" id="addresses" role="tabpanel">
            <div class="row">
                <div class="col-md-6 col-sm-12">
                    <user-address title="Home Address" type="evv" action="{{ route('business.caregivers.address', [$caregiver->id, 'home']) }}" :address="{{ $caregiver->addresses->where('type', 'home')->first() ?? '{}' }}"></user-address>
                </div>
            </div>
        </div>
        <div class="tab-pane" id="phones" role="tabpanel">
            <business-caregiver-phone-numbers-tab :user="{{ $caregiver }}"></business-caregiver-phone-numbers-tab>
        </div>
        <div class="tab-pane" id="bankaccount" role="tabpanel">
            <div class="row">
                <div class="col-lg-6 col-sm-12">
                    <div class="card">
                        <div class="card-header bg-info text-white">Bank Account</div>
                        <div class="card-body">
                            <bank-account-form :account="{{ $caregiver->bankAccount OR '{}' }}" :submit-url="'{{ '/business/caregivers/' . $caregiver->id . '/bank_account' }}'" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="tab-pane" id="schedule" role="tabpanel">
            <business-schedule :caregiver="{{ $caregiver }}"></business-schedule>
        </div>
        <div class="tab-pane" id="caregiver_notes" role="tabpanel">
            <notes-tab :notes="{{ $caregiver->notes }}"></notes-tab>
        </div>
        <div class="tab-pane" id="documents" role="tabpanel">
            <document-list
                :initial-documents="{{ $caregiver->user->documents->toJson() }}"
                :user-id="{{ $caregiver->user->id }}"
            ></document-list>
        </div>
        <div class="tab-pane" id="licenses" role="tabpanel">
            <caregiver-license-list
                    :licenses="{{ $caregiver->licenses }}"
                    :caregiver-id="{{ $caregiver->id }}"
            ></caregiver-license-list>
        </div>
        <div class="tab-pane" id="availability" role="tabpanel">
            <!-- Availability Placeholder -->
            <h4>Availability coming soon</h4>
            <p>This will be where caregivers can set their days and hours of availability.</p>
        </div>
        <div class="tab-pane" id="preferences" role="tabpanel">
            <!-- Preferences Placeholder -->
            <h4>Preferences coming soon</h4>
            <p>This will be where caregivers can set their environment preferences.</p>
        </div>
        <div class="tab-pane" id="emergency_contacts" role="tabpanel">
            <emergency-contacts-tab :emergency-contacts="{{ $caregiver->user->emergencyContacts }}"
                                    :user-id="{{ $caregiver->id }}"></emergency-contacts-tab>
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
