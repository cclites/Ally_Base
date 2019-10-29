<li>
    <a class="has-arrow" href="/home" aria-expanded="false"><i class="mdi mdi-gauge"></i><span class="hide-menu">Dashboard </span></a>
</li>
<li> <a class="has-arrow" href="javascript:void(0)" aria-expanded="false"><i class="fa fa-bank"></i><span class="hide-menu">Businesses</span></a>
    <ul aria-expanded="false" class="collapse">
        <li><a href="{{ route('admin.businesses.chains') }}">Chain List</a></li>
        <li><a href="{{ route('admin.businesses.index') }}">Location List</a></li>
        <li><a href="{{ route('admin.businesses.create') }}">Add Location</a></li>
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
<li> <a class="has-arrow" href="javascript:void(0)" aria-expanded="false"><i class="fa fa-file-pdf-o"></i><span
                class="hide-menu">Invoices/Stubs</span></a>
    <ul aria-expanded="false" class="collapse">
        <li><a href="{{ route('admin.invoices.clients') }}">Client Invoices</a></li>
        <li><a href="{{ route('admin.invoices.deposits') }}">Deposit Stubs</a></li>
    </ul>
</li>
<li>
    <a href="{{ route('admin.reports.index') }}" ><i class="fa fa-bar-chart"></i><span class="hide-menu">Reports</span></a>
</li>
<li>
    <a class="" href="{{ route('admin.knowledge.manager') }}" aria-expanded="false">
        <i class="fa fa-lightbulb-o"></i><span class="hide-menu">Knowledge Base</span>
    </a>
</li>
<li>
    <a class="" href="{{ route('admin.deposits.import') }}" aria-expanded="false">
        <i class="fa fa-money"></i><span class="hide-menu">Adjustment Imports</span>
    </a>
</li>
<li>
    <a class="has-arrow" href="javascript:void(0)" aria-expanded="false"><i class="fa fa-upload"></i><span class="hide-menu">Shift Imports</span></a>
    <ul aria-expanded="false" class="collapse">
        <li><a href="{{ route('admin.import') }}">Shift Importer</a></li>
        <li><a href="{{ route('admin.imports.index') }}">Import History</a></li>
    </ul>
</li>
<li>
    <a class="has-arrow" href="javascript:void(0)" aria-expanded="false"><i class="fa fa-sticky-note"></i><span class="hide-menu">Note Imports</span></a>
    <ul aria-expanded="false" class="collapse">
        <li><a href="{{ route('admin.note-import') }}">Note Importer</a></li>
        {{-- <li><a href="{{ route('admin.imports.index') }}">Import History</a></li> --}}
    </ul>
</li>
<li>
    <a class="has-arrow" href="javascript:void(0)" aria-expanded="false"><i class="fa fa-diamond"></i><span class="hide-menu">Testing</span></a>
    <ul aria-expanded="false" class="collapse">
        <li><a href="{{ route('admin.microbilt') }}">Microbilt</a></li>
        <li><a href="{{ route('admin.nacha_ach') }}">Nacha Ach</a></li>
        <li><a href="{{ route('admin.communication-log') }}">Communication Log</a></li>
        <li><a href="{{ route('admin.control-file') }}">Control File</a></li>
    </ul>
</li>
