<li>
    <a class="has-arrow" href="/home" aria-expanded="false"><i class="mdi mdi-gauge"></i><span class="hide-menu">Dashboard </span></a>
</li>
<li>
    <a class="has-arrow" href="{{ route('check_in') }}" aria-expanded="false"><i class="fa fa-calendar-check-o"></i><span class="hide-menu">Check In</span></a>
</li>
<li> <a class="has-arrow" href="{{ route('schedule') }}" aria-expanded="false"><i class="fa fa-calendar"></i><span class="hide-menu">Schedule</span></a>
</li>
<li> <a class="has-arrow" href="javascript:void()" aria-expanded="false"><i class="fa fa-bar-chart"></i><span class="hide-menu">Reports</span></a>
    <ul aria-expanded="false" class="collapse">
        <li><a href="{{ route('caregivers.reports.payments') }}">Payment History</a></li>
        <li><a href="{{ route('caregivers.reports.shifts') }}">Shift History</a></li>
    </ul>
</li>