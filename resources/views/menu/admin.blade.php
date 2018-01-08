<li>
    <a class="has-arrow" href="/home" aria-expanded="false"><i class="mdi mdi-gauge"></i><span class="hide-menu">Dashboard </span></a>
</li>
<li> <a class="has-arrow" href="javascript:void()" aria-expanded="false"><i class="fa fa-bank"></i><span class="hide-menu">Businesses</span></a>
    <ul aria-expanded="false" class="collapse">
        <li><a href="{{ route('admin.businesses.index') }}">Provider List</a></li>
        <li><a href="{{ route('admin.businesses.create') }}">Add Provider</a></li>
    </ul>
</li>
<li> <a class="has-arrow" href="javascript:void()" aria-expanded="false"><i class="fa fa-users"></i><span class="hide-menu">Users</span></a>
    <ul aria-expanded="false" class="collapse">
        <li><a href="{{ route('admin.users.index') }}">User List</a></li>
    </ul>
</li>
<li> <a class="has-arrow" href="javascript:void()" aria-expanded="false"><i class="fa fa-money"></i><span class="hide-menu">Charges</span></a>
    <ul aria-expanded="false" class="collapse">
        <li><a href="{{ route('admin.charges.pending_shifts') }}">Pending Shifts</a></li>
        <li><a href="{{ route('admin.charges.pending') }}">Pending Charges</a></li>
        <li><a href="{{ route('admin.charges') }}">Charges Report</a></li>
        <li><a href="{{ route('admin.transactions') }}">Transactions Report</a></li>
    </ul>
</li>
<li> <a class="has-arrow" href="javascript:void()" aria-expanded="false"><i class="fa fa-money"></i><span class="hide-menu">Deposits</span></a>
    <ul aria-expanded="false" class="collapse">
        <li><a href="{{ route('admin.deposits.pending') }}">Pending Deposits</a></li>
        <li><a href="{{ route('admin.deposits') }}">Deposit Report</a></li>
    </ul>
</li>
<li>
    <a class="has-arrow" href="javascript:void(0)" aria-expanded="false"><i class="fa fa-bar-chart"></i><span class="hide-menu">Reports</span></a>
    <ul aria-expanded="false" class="collapse">
        <li><a href="{{ route('admin.reports.unsettled') }}">Unsettled Report</a></li>
    </ul>
</li>


