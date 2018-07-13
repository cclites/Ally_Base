<!-- <li>
    <a class="has-arrow" href="/home" aria-expanded="false"><i class="mdi mdi-gauge"></i><span class="hide-menu">Dashboard </span></a>
</li> -->
<li>
    <a class="has-arrow" href="/business/schedule" aria-expanded="false"><i class="fa fa-calendar"></i><span class="hide-menu">Schedule </span></a>
</li>
<li>
    <a class="has-arrow" href="{{ route('business.clients.index') }}" aria-expanded="false"><i class="fa fa-users"></i><span class="hide-menu">Clients</span></a>
    <ul aria-expanded="false" class="collapse">
        <li><a href="{{ route('business.clients.index') }}">Client List</a></li>
        <li><a href="{{ route('business.clients.create') }}">Add Client</a></li>
    </ul>
</li>
<li>
    <a class="has-arrow" href="{{ route('business.caregivers.index') }}" aria-expanded="false"><i class="fa fa-user"></i><span class="hide-menu">Caregivers</span></a>
    <ul aria-expanded="false" class="collapse">
        <li><a href="{{ route('business.caregivers.index') }}">Caregiver List</a></li>
        <li><a href="{{ route('business.caregivers.create') }}">Add Caregiver</a></li>
        <li><a href="{{ route('business.caregivers.distance_report') }}">Distance Report</a></li>
        <li><a href="{{ route('business.caregivers.applications') }}">Applications</a></li>
    </ul>
</li>
<li>
    <a class="has-arrow" href="javascript:void(0)" aria-expanded="false"><i class="fa fa-sticky-note"></i><span class="hide-menu">Notes</span></a>
    <ul aria-expanded="false" class="collapse">
        <li><a href="/notes">Notes</a></li>
        <li><a href="/notes/create">Add Notes</a></li>
    </ul>
</li>
 <li>
    <a class="has-arrow" href="{{ route('business.reports.shifts') }}?autoload=0" href="javascript:void(0)" aria-expanded="false"><i class="fa fa-clock-o"></i><span class="hide-menu">Shift History</span></a>
</li>
<li>
    <a class="has-arrow" href="javascript:void(0)" aria-expanded="false"><i class="fa fa-bar-chart"></i><span class="hide-menu">Reports</span></a>
    <ul aria-expanded="false" class="collapse">
        <li><a href="{{ route('business.reports.payments') }}">Payment History</a></li>
        {{-- <li><a href="{{ route('business.reports.shifts') }}">Shift History</a></li> --}}
        {{--<li><a href="{{ route('business.reports.scheduled') }}">Scheduled Payments</a></li>--}}
        <li><a href="{{ route('business.reports.reconciliation') }}">Reconciliation Report</a></li>
        {{--<li><a href="{{ route('business.reports.medicaid') }}">Medicaid Payroll</a></li>--}}
        <li><a href="{{ route('business.reports.overtime') }}">Caregiver Overtime</a></li>
        <li><a href="{{ route('business.reports.certification_expirations') }}">Certification Expirations</a></li>
        <li><a href="{{ route('business.reports.cc_expiration') }}">Credit Card Expiration</a></li>
        <li><a href="{{ route('business.reports.client_caregivers') }}">Client Caregiver Rates</a></li>
        <li><a href="{{ route('business.reports.client_email_missing') }}">Clients without Email</a></li>
        <li><a href="{{ route('business.reports.client_onboarded') }}">Client Online Setup</a></li>
        <li><a href="{{ route('business.reports.caregiver_onboarded') }}">Caregiver Online Setup</a></li>
        <li><a href="{{ route('business.reports.printable_schedule') }}">Printable Schedule</a></li>
        <li><a href="{{ route('business.reports.caregivers_missing_bank_accounts') }}">Missing Bank Accounts</a></li>
        <li><a href="{{ route('business.reports.export_timesheets') }}">Export Timesheets</a></li>
        <li><a href="{{ route('business.reports.claims_report') }}">Claims Report</a></li>
    </ul>
</li>
@if (activeBusiness() && activeBusiness()->allows_manual_shifts)
<li>
    <a class="has-arrow" href="{{ route('business.timesheet.create') }}" aria-expanded="false"><i class="fa fa-calendar-plus-o"></i><span class="hide-menu">Enter Timesheet</span></a>
</li>
@endif
<li>
    <a href="{{ route('business.exceptions.index') }}">
        <i class="fa fa-exclamation"></i>
        <span class="hide-menu">Exceptions</span>
        <span class="badge badge-danger badge-notifications menu-badge">
            {{ \App\SystemException::notAcknowledged()->count() }}
        </span>
    </a>
</li>
<li>
    <a class="has-arrow" href="javascript:void(0)" aria-expanded="false"><i class="fa fa-gear"></i><span class="hide-menu">Settings </span></a>
    <ul aria-expanded="false" class="collapse">
        <li><a href="{{ route('business.settings.index') }}">General</a></li>
        <li><a href="{{ route('business.settings.bank_accounts.index') }}">Bank Accounts</a></li>
        <li><a href="{{ route('business.activities.index') }}">Activities</a></li>
    </ul>
</li>
