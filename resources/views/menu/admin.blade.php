

<li>
    <a class="has-arrow" href="/home" aria-expanded="false"><i class="mdi mdi-gauge"></i><span class="hide-menu">Dashboard </span></a>
</li>
<li> <a class="has-arrow" href="javascript:void(0)" aria-expanded="false"><i class="fa fa-bank"></i><span class="hide-menu">Businesses</span></a>
    <ul aria-expanded="false" class="collapse">
        <li><a href="{{ route('admin.businesses.index') }}">Provider List</a></li>
        <li><a href="{{ route('admin.businesses.create') }}">Add Provider</a></li>
        <li class="divider"><hr style="margin:0;"/></li>
        <li><a href="{{ route('business.caregivers.index') }}">Caregiver List</a></li>
        <li><a href="{{ route('business.clients.index') }}">Client List</a></li>
        <li class="divider"><hr style="margin:0;"/></li>
        <li><a href="{{ route('business.reports.shifts') }}?autoload=0">Shift History Report</a></li>
    </ul>
</li>
<li> <a class="has-arrow" href="javascript:void(0)" aria-expanded="false"><i class="fa fa-users"></i><span class="hide-menu">Users</span></a>
    <ul aria-expanded="false" class="collapse">
        <li><a href="{{ route('admin.users.index') }}">User List</a></li>
    </ul>
</li>
<li> <a class="has-arrow" href="javascript:void(0)" aria-expanded="false"><i class="fa fa-money"></i><span class="hide-menu">Charges</span></a>
    <ul aria-expanded="false" class="collapse">
        <li><a href="{{ route('admin.charges.pending_shifts') }}">Pending Shifts</a></li>
        <li><a href="{{ route('admin.charges.pending') }}">Pending Charges</a></li>
        <li><a href="{{ route('admin.charges') }}">Charges Report</a></li>
        <li><a href="{{ route('admin.transactions') }}">Transactions Report</a></li>
        <li><a href="{{ route('admin.missing_transactions') }}">Missing Transactions</a></li>
        <li><a href="{{ route('admin.charges.manual') }}">Manual Adjustment</a></li>
    </ul>
</li>
<li> <a class="has-arrow" href="javascript:void(0)" aria-expanded="false"><i class="fa fa-money"></i><span class="hide-menu">Deposits</span></a>
    <ul aria-expanded="false" class="collapse">
        <li><a href="{{ route('admin.deposits.pending') }}">Pending Deposits</a></li>
        <li><a href="{{ route('admin.deposits') }}">Deposit Report</a></li>
        <li><a href="{{ route('admin.deposits.failed') }}">Failed Deposits</a></li>
        <li><a href="{{ route('admin.deposits.adjustment') }}">Manual Adjustment</a></li>
    </ul>
</li>
<li>
    <a class="has-arrow" href="javascript:void(0)" aria-expanded="false"><i class="fa fa-bar-chart"></i><span class="hide-menu">Reports</span></a>
    <ul aria-expanded="false" class="collapse">
        <li><a href="{{ route('admin.reports.unsettled') }}">Unsettled Report</a></li>
        <li><a href="{{ route('admin.reports.reconciliation') }}">Reconciliation Report</a></li>
        <li><a href="{{ route('admin.failed_transactions.index') }}">Failed Transactions</a></li>
        <li><a href="{{ route('admin.reports.pending_transactions') }}">Pending Transactions</a></li>
        <li><a href="{{ route('admin.reports.on_hold') }}">On Hold Report</a></li>
        <li><a href="{{ route('admin.deposits.failed') }}">Failed Deposits</a></li>
        <li><a href="{{ route('admin.reports.shared_shifts') }}">Shared Shifts</a></li>
        <li><a href="{{ route('admin.reports.unpaid_shifts') }}">Unpaid Shifts</a></li>
        <li><a href="{{ route('admin.reports.caregivers.deposits_missing_bank_account') }}">Missing Deposit Accounts</a></li>
        <li><a href="{{ route('admin.reports.finances') }}">Financial Summary</a></li>
        <li><a href="{{ route('admin.reports.client_caregiver_visits') }}">Client Caregiver Visits</a></li>
        <li><a href="{{ route('admin.reports.active_clients') }}">Active Clients Report</a></li>
        <li><a href="{{ route('admin.reports.bucket') }}">Bucket Report</a></li>
        <li><a href="{{ route('admin.reports.evv') }}">EVV Report</a></li>
    </ul>
</li>
<li>
    <a class="has-arrow" href="javascript:void(0)" aria-expanded="false"><i class="fa fa-upload"></i><span class="hide-menu">Shift Imports</span></a>
    <ul aria-expanded="false" class="collapse">
        <li><a href="{{ route('admin.import') }}">Shift Importer</a></li>
        <li><a href="{{ route('admin.imports.index') }}">Import History</a></li>
    </ul>
</li>


