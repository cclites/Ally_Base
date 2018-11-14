<!-- <li>
    <a class="has-arrow" href="/home" aria-expanded="false"><i class="mdi mdi-gauge"></i><span class="hide-menu">Dashboard </span></a>
</li> -->
<li>
    <a href="/business/schedule" aria-expanded="false"><i class="fa fa-calendar"></i><span class="hide-menu">Schedule </span></a>
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
        {{--<li><a href="{{ route('business.caregivers.distance_report') }}">Distance Report</a></li>--}}
        <li><a href="{{ route('business.caregivers.applications') }}">Applications</a></li>
    </ul>
</li>
<li>
    <a class="has-arrow" href="{{ route('business.prospects.index') }}" aria-expanded="false"><i class="fa fa-user-plus"></i><span class="hide-menu">Prospects</span></a>
    <ul aria-expanded="false" class="collapse">
        <li><a href="{{ route('business.prospects.index') }}">Prospect List</a></li>
        <li><a href="{{ route('business.prospects.create') }}">Add Prospect</a></li>
    </ul>
</li>
<li>
    <a href="{{ route('business.care-match') }}" ><i class="fa fa-clone"></i><span class="hide-menu">Care Match</span></a>
</li>
<li>
    <a class="has-arrow" href="javascript:void(0)" aria-expanded="false"><i class="fa fa-sticky-note"></i><span class="hide-menu">Notes</span></a>
    <ul aria-expanded="false" class="collapse">
        <li><a href="/notes">Notes</a></li>
        <li><a href="/notes/create">Add Notes</a></li>
    </ul>
</li>
@if(activeBusiness()->use_rate_codes)
    <li>
        <a href="{{ route('business.rate-codes.index') }}" aria-expanded="false"><i class="fa fa-list-alt"></i><span class="hide-menu">Rate Codes</span></a>
    </li>
@endif
<li>
    <a href="{{ route('business.reports.shifts') }}?autoload=0" aria-expanded="false"><i class="fa fa-clock-o"></i><span class="hide-menu">Shift History</span></a>
</li>
<li>
    <a class="has-arrow" href="javascript:void(0)" aria-expanded="false">
        <i class="fa fa-usd" style="margin-left: 3px; margin-right: -3px;"></i><span class="hide-menu">Accounting</span>
    </a>
    <ul aria-expanded="false" class="collapse">
        <li><a href="{{ route('business.accounting.apply-payment.index') }}">Receivables</a></li>
        <li><a href="{{ route('business.accounting.claims') }}">Claims</a></li>
        @if(app()->environment() === 'demo')
            <li><a href="{{ route('business.quickbooks.index') }}">Export to Quickbooks</a></li>
        @endif
    </ul>
</li>
<li>
    <a href="{{ route('business.reports.index') }}" ><i class="fa fa-bar-chart"></i><span class="hide-menu">Reports</span></a>
    {{--<a class="has-arrow" href="javascript:void(0)" aria-expanded="false"><i class="fa fa-bar-chart"></i><span class="hide-menu">Reports</span></a>--}}
    {{--<ul aria-expanded="false" class="collapse">--}}
    {{--<li><a href="{{ route('business.reports.payments') }}">Payment History</a></li>--}}
    {{-- <li><a href="{{ route('business.reports.shifts') }}">Shift History</a></li> --}}
    {{--<li><a href="{{ route('business.reports.scheduled') }}">Scheduled Payments</a></li>--}}
    {{--<li><a href="{{ route('business.reports.reconciliation') }}">Reconciliation Report</a></li>--}}
    {{--<li><a href="{{ route('business.reports.medicaid') }}">Medicaid Payroll</a></li>--}}
    {{--<li><a href="{{ route('business.reports.overtime') }}">Caregiver Overtime</a></li>--}}
    {{--<li><a href="{{ route('business.reports.certification_expirations') }}">Certification Expirations</a></li>--}}
    {{--<li><a href="{{ route('business.reports.cc_expiration') }}">Credit Card Expiration</a></li>--}}
    {{--<li><a href="{{ route('business.reports.client_caregivers') }}">Client Caregiver Rates</a></li>--}}
    {{--<li><a href="{{ route('business.reports.client_email_missing') }}">Clients without Email</a></li>--}}
    {{--<li><a href="{{ route('business.reports.client_onboarded') }}">Client Online Setup</a></li>--}}
    {{--<li><a href="{{ route('business.reports.caregiver_onboarded') }}">Caregiver Online Setup</a></li>--}}
    {{--<li><a href="{{ route('business.reports.printable_schedule') }}">Printable Schedule</a></li>--}}
    {{--<li><a href="{{ route('business.reports.caregivers_missing_bank_accounts') }}">Missing Bank Accounts</a></li>--}}
    {{--<li><a href="{{ route('business.reports.export_timesheets') }}">Export Timesheets</a></li>--}}
    {{--<li><a href="{{ route('business.reports.claims_report') }}">Claims Report</a></li>--}}
    {{--</ul>--}}
</li>
@if (activeBusiness() && activeBusiness()->allows_manual_shifts)
    <li>
        <a href="{{ route('business.timesheet.create') }}" aria-expanded="false"><i class="fa fa-calendar-plus-o"></i><span class="hide-menu">Enter Timesheet</span></a>
    </li>
@endif

<li>
    <a class="has-arrow" href="javascript:void(0)" aria-expanded="false">
        <i class="fa fa-envelope"></i><span class="hide-menu">Communication</span>
    </a>
    <ul aria-expanded="false" class="collapse">
        <li><a href="{{ route('business.communication.text-caregivers') }}">Text Caregivers</a></li>
        <li><a href="{{ route('business.communication.sms-threads') }}">Sent Texts</a></li>
    </ul>
</li>
<li>
    <a href="{{ route('business.tasks.index') }}">
        <div class="row">
            <div class="col-8">
                <i class="fa fa-check-square-o"></i><span class="hide-menu">Tasks</span>
            </div>
            <div class="col-4">
                <span class="badge badge-warning badge-notifications hide-menu menu-badge">{{ auth()->user()->dueTasks()->count() }}</span>
            </div>
        </div>
    </a>
</li>
<li>
    <a href="{{ route('business.exceptions.index') }}">
        <div class="row">
            <div class="col-8">
                <i class="fa fa-exclamation" style="margin-left: 6px; margin-right: -6px;"></i><span class="hide-menu">Exceptions</span>
            </div>
            <div class="col-4">
                    <span class="badge badge-danger badge-notifications hide-menu menu-badge">
                        {{ activeBusiness()->exceptions()->notAcknowledged()->count() }}
                    </span>
            </div>
        </div>
    </a>
</li>
<li>
    <a class="has-arrow" href="javascript:void(0)" aria-expanded="false"><i class="fa fa-cart-arrow-down"></i><span class="hide-menu">Referral Sources </span></a>
    <ul aria-expanded="false" class="collapse">
        <li><a href="{{ route('business.referral-sources.index') }}">List Sources</a></li>
        <li><a href="{{ route('business.referral-sources.create') }}">Add Source</a></li>
{{--        <li><a href="{{ route('business.reports.referral_sources') }}">Reports Referral Sources</a></li>--}}
    </ul>
</li>
<li>
    <a class="has-arrow" href="javascript:void(0)" aria-expanded="false"><i class="fa fa-gear"></i><span class="hide-menu">Settings </span></a>
    <ul aria-expanded="false" class="collapse">
        <li><a href="{{ route('business.settings.index') }}">General</a></li>
        <li><a href="{{ route('business.settings.bank_accounts.index') }}">Bank Accounts</a></li>
        <li><a href="{{ route('business.activities.index') }}">Activities</a></li>
        <li><a href="{{ route('business.quickbooks.index') }}">Quickbooks</a></li>
    </ul>
</li>
