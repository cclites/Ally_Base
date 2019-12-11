@extends('layouts.app')

@section('title', $caregiver->name())

@section('avatar')
    <user-avatar src="{{ $caregiver->avatar }}" title="{{ $caregiver->name() }}" size="50"></user-avatar>
@endsection

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('business.caregivers.index') }}">Caregivers</a></li>
    <li class="breadcrumb-item active">{{ $caregiver->name() }}</li>
@endsection

@section('content')
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
            <a class="nav-link" data-toggle="tab" href="#bankaccount" role="tab">Direct Deposit</a>
        </li>
        @if ($business->scheduling)
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#schedule" role="tab">Schedule</a>
            </li>
        @endif
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#clients" role="tab">Clients</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#office-locations" role="tab">Office Locations</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#caregiver_notes" role="tab">Notes</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#documents" role="tab">Documents</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#licenses" role="tab">Expirations</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#availability" role="tab">Availability</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#skills" role="tab">Skills</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#emergency_contacts" role="tab">Emergency Contacts</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#payment_statement" role="tab">Pay Statements</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#misc" role="tab">Misc.</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#notifications" role="tab">Notifications</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#restrictions" role="tab">Restrictions</a>
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
                <a class="dropdown-item" data-toggle="tab" href="#bankaccount" role="tab">Direct Deposit</a>
                @if($business->scheduling)
                    <a class="dropdown-item" data-toggle="tab" href="#schedule" role="tab">Schedule</a>
                @endif
                <a class="dropdown-item" data-toggle="tab" href="#clients" role="tab">Clients</a>
                <a class="dropdown-item" data-toggle="tab" href="#office-locations" role="tab">Office Locations</a>
                <a class="dropdown-item" data-toggle="tab" href="#caregiver_notes" role="tab">Notes</a>
                <a class="dropdown-item" data-toggle="tab" href="#documents" role="tab">Documents</a>
                <a class="dropdown-item" data-toggle="tab" href="#licenses" role="tab">Expirations</a>
                <a class="dropdown-item" data-toggle="tab" href="#availability" role="tab">Availability</a>
                <a class="dropdown-item" data-toggle="tab" href="#skills" role="tab">Skills</a>
                <a class="dropdown-item" data-toggle="tab" href="#emergency_contacts" role="tab">Emergency Contacts</a>
                <a class="dropdown-item" data-toggle="tab" href="#payment_statement" role="tab">Pay Statements</a>
                <a class="dropdown-item" data-toggle="tab" href="#misc" role="tab">Misc.</a>
                <a class="dropdown-item" data-toggle="tab" href="#notifications" role="tab">Notifications</a>
                <a class="dropdown-item" data-toggle="tab" href="#restrictions" role="tab">Restrictions</a>
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
                    <user-address title="Home Address" type="home" action="{{ route('business.caregivers.address', [$caregiver->id, 'home']) }}" :address="{{ $caregiver->addresses->where('type', 'home')->first() ?? '{}' }}"></user-address>
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
            <business-schedule :caregiver="{{ $caregiver }}" :business="{{ activeBusiness() }}"></business-schedule>
        </div>
        <div class="tab-pane" id="clients" role="tabpanel">
            <business-caregiver-clients-tab :caregiver="{{ $caregiver }}"></business-caregiver-clients-tab>
        </div>
        <div class="tab-pane" id="office-locations" role="tabpanel">
            <business-caregiver-office-locations-tab :caregiver="{{ $caregiver }}"></business-caregiver-office-locations-tab>
        </div>
        <div class="tab-pane" id="caregiver_notes" role="tabpanel">

            <div class="alert alert-warning">
                <h4>Notes are visible to Office Users only</h4>
            </div>
            <notes-tab :notes="{{ $caregiver->notes }}" :business="{{ $business }}" :caregiver="{{ $caregiver }}">
            </notes-tab>
        </div>
        <div class="tab-pane" id="documents" role="tabpanel">
            <document-list
                :initial-documents="{{ $caregiver->user->documents->toJson() }}"
                :user-id="{{ $caregiver->user->id }}"
                :active="{{ $caregiver->active }}"
                type="caregiver"
            ></document-list>
        </div>
        <div class="tab-pane" id="licenses" role="tabpanel">
            <caregiver-license-list
                    :licenses="{{ $caregiver->licenses }}"
                    :caregiver-id="{{ $caregiver->id }}"
            ></caregiver-license-list>
        </div>
        <div class="tab-pane" id="availability" role="tabpanel">
            <business-caregiver-availability-tab :caregiver="{{ $caregiver }}" updated-by="{{ $caregiver->availability->updatedByUser->name ?? '' }}"></business-caregiver-availability-tab>
        </div>
        <div class="tab-pane" id="skills" role="tabpanel">
            <business-caregiver-skills-tab :caregiver="{{ $caregiver }}"></business-caregiver-skills-tab>
        </div>
        <div class="tab-pane" id="emergency_contacts" role="tabpanel">
            <contact-list-tab :emergency-contacts="{{ $caregiver->user->emergencyContacts }}"
                                    :user-id="{{ $caregiver->id }}"></contact-list-tab>
        </div>
        <div class="tab-pane" id="payment_statement" role="tabpanel">
            <!-- Check access to caregiver statements tab via Gate -->
            @can('view-caregiver-statements', $caregiver)
                <business-caregiver-pay-statements :caregiver="{{ $caregiver }}" :deposits="{{ $caregiver->deposits }}"></business-caregiver-pay-statements>
            @else
                <div class="card">
                    <div class="card-body">
                        <div class="alert alert-warning">
                            <h4>You do not have access to this caregiver's payment statements.</h4>
                            Due to this caregiver working for a location you do not have access to, you cannot view the caregiver's payment statements.  Payment statements from Ally can include shifts from multiple locations.
                        </div>
                    </div>
                </div>
            @endcan
        </div>
        <div class="tab-pane" id="misc" role="tabpanel">
            <business-caregiver-misc-tab misc="{{ $caregiver->misc }}" :caregiver="{{ $caregiver }}"></business-caregiver-misc-tab>
        </div>
        <div class="tab-pane" id="notifications" role="tabpanel">
            <notification-preferences :admin="true" :user="{{ $caregiver->user }}" :notifications="{{ $notifications }}"></notification-preferences>
        </div>
        <div class="tab-pane" id="restrictions" role="tabpanel">
            <business-caregiver-restrictions-tab :caregiver="{{ $caregiver }}"></business-caregiver-restrictions-tab>
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
            $('.profile-tabs .nav-item a[href="#' + url.split('#')[1] + '"]').tab('show');
        }

        // Change hash for page-reload
        $('.profile-tabs .nav-item a').on('shown.bs.tab', function (e) {
            history.pushState({}, '', url.split('#')[0] + e.target.hash);
        })
    </script>
@endpush
