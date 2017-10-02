<li>
    <a class="has-arrow" href="/home" aria-expanded="false"><i class="mdi mdi-gauge"></i><span class="hide-menu">Dashboard </span></a>
</li>
<li>
    <a class="has-arrow" href="/business/schedule" aria-expanded="false"><i class="fa fa-calendar"></i><span class="hide-menu">Schedule </span></a>
</li>
<li> <a class="has-arrow" href="{{ route('business.clients.index') }}" aria-expanded="false"><i class="fa fa-users"></i><span class="hide-menu">Clients</span></a>
    <ul aria-expanded="false" class="collapse">
        <li><a href="{{ route('business.clients.index') }}">Client List</a></li>
        <li><a href="{{ route('business.clients.create') }}">Add Client</a></li>
    </ul>
</li>
<li> <a class="has-arrow" href="{{ route('business.caregivers.index') }}" aria-expanded="false"><i class="fa fa-user"></i><span class="hide-menu">Caregivers</span></a>
    <ul aria-expanded="false" class="collapse">
        <li><a href="{{ route('business.caregivers.index') }}">Caregiver List</a></li>
        <li><a href="{{ route('business.caregivers.create') }}">Add Caregiver</a></li>
    </ul>
</li>
<li> <a class="has-arrow" href="javascript:void()" aria-expanded="false"><i class="fa fa-bar-chart"></i><span class="hide-menu">Reports</span></a>
    <ul aria-expanded="false" class="collapse">
        <li><a href="{{ route('business.reports.payments') }}">Payment History</a></li>
        <li><a href="{{ route('business.reports.shifts') }}">Shift History</a></li>
        <li><a href="{{ route('business.reports.scheduled') }}">Scheduled Payments</a></li>
        <li><a href="{{ route('business.reports.deposits') }}">Deposit History</a></li>
    </ul>
</li>