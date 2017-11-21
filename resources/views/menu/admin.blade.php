<li>
    <a class="has-arrow" href="/home" aria-expanded="false"><i class="mdi mdi-gauge"></i><span class="hide-menu">Dashboard </span></a>
</li>
<li> <a class="has-arrow" href="javascript:void()" aria-expanded="false"><i class="fa fa-bank"></i><span class="hide-menu">Businesses</span></a>
    <ul aria-expanded="false" class="collapse">
        <li><a href="{{ route('admin.businesses.index') }}">Business List</a></li>
        <li><a href="{{ route('admin.businesses.create') }}">Add Business</a></li>
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
        <li><a href="{{ route('admin.charges.pending_payments') }}">Pending Charges</a></li>
    </ul>
</li>
<li> <a class="has-arrow" href="javascript:void()" aria-expanded="false"><i class="fa fa-money"></i><span class="hide-menu">Deposits</span></a>
    <ul aria-expanded="false" class="collapse">
        <li><a href="{{ route('admin.deposits.pending') }}">Pending Deposits</a></li>
    </ul>
</li>



