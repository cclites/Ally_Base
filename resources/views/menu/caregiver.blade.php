<li>
    @if(auth()->user()->role->isClockedIn())
        <a class="has-arrow" href="{{ route('clock_out') }}" aria-expanded="false"><i class="fa fa-calendar-check-o"></i><span class="hide-menu">Clock Out</span></a>
    @else
        <a class="has-arrow" href="{{ route('clock_in') }}" aria-expanded="false"><i class="fa fa-calendar-check-o"></i><span class="hide-menu">Clock In</span></a>
    @endif
</li>
<li>
    <a class="has-arrow" href="{{ route('schedule') }}" aria-expanded="false"><i class="fa fa-calendar"></i><span class="hide-menu">Schedule</span></a>
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
        <li><a href="{{ route('caregivers.reports.payment_history') }}">Payment History</a></li>
    </ul>
</li>
<li>
    <a href="{{ route('caregivers.tasks') }}">
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
    <a href="/profile" aria-expanded="false">
        <i class="mdi mdi-account-circle"></i><span class="hide-menu">My Information</span>
    </a>
</li>
