<li>
    <a class="has-arrow" href="{{ route('clock_in') }}" aria-expanded="false"><i class="fa fa-calendar-check-o"></i><span class="hide-menu">Clock In</span></a>
</li>
@if(auth()->user()->role->isClockedIn())
    <li>
        <a class="has-arrow" href="{{ route('clock_out') }}" aria-expanded="false"><i class="fa fa-calendar-check-o"></i><span class="hide-menu">Clock Out</span></a>
    </li>
@endif
<li> <a class="has-arrow" href="javascript:void()" aria-expanded="false"><i class="fa fa-calendar"></i><span class="hide-menu">Schedule</span></a>
    <ul aria-expanded="false" class="collapse">
        <li><a href="{{ route('schedule') }}">Calendar</a></li>
        @can( 'view-open-shifts' )

            <li><a href="{{ route('schedule.open-shifts') }}">Open Shifts</a></li>
        @endcan
    </ul>
</li>
<li>
</li>
@if (activeBusiness() && activeBusiness()->allows_manual_shifts)
<li> <a class="has-arrow" href="javascript:void()" aria-expanded="false"><i class="fa fa-clock-o"></i><span class="hide-menu">Timesheets</span></a>
    <ul aria-expanded="false" class="collapse">
        <li><a href="{{ route('timesheets.create') }}">Enter Timesheet</a></li>
        <li><a href="{{ route('timesheets.index') }}">Past Timesheets</a></li>
    </ul>
</li>
@endif
<li> <a class="has-arrow" href="javascript:void()" aria-expanded="false"><i class="fa fa-bar-chart"></i><span class="hide-menu">Reports</span></a>
    <ul aria-expanded="false" class="collapse">
        <li><a href="{{ route('caregivers.reports.shifts') }}">Shift History</a></li>
        <li><a href="{{ route('caregiver.deposits') }}">Payment History</a></li>
    </ul>
</li>
<li>
    <a href="/profile" aria-expanded="false">
        <i class="mdi mdi-account-circle"></i><span class="hide-menu">My Information</span>
    </a>
</li>
<li>
    <a href="{{ route('caregivers.clients') }}?active=1" aria-expanded="false">
        <i class="fa fa-users"></i><span class="hide-menu">My Clients</span>
    </a>
</li>
<li>
    <a class="" href="{{ route('knowledge.base') }}" aria-expanded="false">
        <i class="fa fa-lightbulb-o"></i><span class="hide-menu">Knowledge Base</span>
    </a>
</li>