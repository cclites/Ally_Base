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
@if($active_business->scheduling)
<li>
    <a class="has-arrow" href="javascript:void(0)" aria-expanded="false"><i class="fa fa-medkit"></i><span class="hide-menu">Care Plans</span></a>
    <ul aria-expanded="false" class="collapse">
        <li><a href="{{ route('business.care_plans.index') }}">Care Plans</a></li>
        <li><a href="{{ route('business.activities.index') }}">Activities</a></li>
        <li class="divider"></li>
        <li><a href="{{ route('business.care_plans.create') }}">Add a Care Plan</a></li>
    </ul>
</li>
@endif
<li>
    <a class="has-arrow" href="javascript:void(0)" aria-expanded="false"><i class="fa fa-sticky-note"></i><span class="hide-menu">Notes</span></a>
    <ul aria-expanded="false" class="collapse">
        <li><a href="/notes">Notes</a></li>
        <li><a href="/notes/create">Add Notes</a></li>
    </ul>
</li>
<li>
    <a class="has-arrow" href="javascript:void(0)" aria-expanded="false"><i class="fa fa-bar-chart"></i><span class="hide-menu">Reports</span></a>
    <ul aria-expanded="false" class="collapse">
        <li><a href="{{ route('business.reports.payments') }}">Payment History</a></li>
        <li><a href="{{ route('business.reports.shifts') }}">Shift / Activity History</a></li>
        <li><a href="{{ route('business.reports.scheduled') }}">Scheduled Payments</a></li>
        <li><a href="{{ route('business.reports.deposits') }}">Registry Referral Deposits</a></li>
        <li><a href="{{ route('business.reports.medicaid') }}">Medicaid Payroll</a></li>
        <li><a href="{{ route('business.reports.overtime') }}">Caregiver Overtime Report</a></li>
        <li><a href="{{ route('business.reports.certification_expirations') }}">Certification Expirations</a></li>
    </ul>
</li>
<li>
    <a class="has-arrow" href="javascript:void(0)" aria-expanded="false"><i class="fa fa-gear"></i><span class="hide-menu">Settings </span></a>
    <ul aria-expanded="false" class="collapse">
        <li><a href="{{ route('business.settings.index') }}">General</a></li>
        <li><a href="{{ route('business.settings.bank_accounts.index') }}">Bank Accounts</a></li>
    </ul>
</li>
