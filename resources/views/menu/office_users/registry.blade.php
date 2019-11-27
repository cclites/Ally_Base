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
{{--<li>--}}
    {{--<a class="has-arrow" href="{{ route('business.contacts.index') }}" aria-expanded="false"><i class="fa fa-user-plus"></i><span class="hide-menu">Contacts</span></a>--}}
    {{--<ul aria-expanded="false" class="collapse">--}}
        {{--<li><a href="{{ route('business.contacts.index') }}">Contact List</a></li>--}}
        {{--<li><a href="{{ route('business.contacts.create') }}">Add Contact</a></li>--}}
    {{--</ul>--}}
{{--</li>--}}
<li>
    <a href="{{ route('business.care-match') }}" ><i class="fa fa-clone"></i><span class="hide-menu">Care Match</span></a>
</li>
<li>
    <a class="has-arrow" href="javascript:void(0)" aria-expanded="false"><i class="fa fa-sticky-note"></i><span class="hide-menu">Call Center</span></a>
    <ul aria-expanded="false" class="collapse">
        <li><a href="/notes">Notes</a></li>
        <li><a href="/notes/create">Add Note</a></li>
        <li><a href="/note-templates">Templates</a></li>
    </ul>
</li>
<li>
    <a href="{{ route('business.reports.shifts') }}?autoload=0" aria-expanded="false"><i class="fa fa-clock-o"></i><span class="hide-menu">Shift History</span></a>
</li>
<li>
    <a class="has-arrow" href="javascript:void(0)" aria-expanded="false">
        <i class="fa fa-building-o" style="margin-left: 3px; margin-right: -3px;"></i><span class="hide-menu">Billing</span>
    </a>
    <ul aria-expanded="false" class="collapse">
        <li><a href="{{ route('business.payers.index') }}">Payers</a></li>
        <li><a href="{{ route('business.services.index') }}">Service Codes</a></li>
        @if(activeBusiness()->use_rate_codes)
            <li><a href="{{ route('business.rate-codes.index') }}">Rate Codes</a></li>
        @endif
        <li><a href="{{ route('business.claims-ar') }}">Claims & AR</a></li>
        <li><a href="{{ route('business.offline-invoice-ar') }}">Offline Invoice AR</a></li>
        <li><a href="{{ route('business.quickbooks-queue') }}">Quickbooks Queue</a></li>
        {{-- <li><a href="{{ route('business.accounting.claims') }}">Claims</a></li> --}}
        {{-- <li><a href="{{ route('business.accounting.apply-payment.index') }}">Receivables</a></li> --}}
        {{-- @if(app()->environment() === 'demo') --}}
            {{-- <li><a href="{{ route('business.quickbooks.index') }}">Export to Quickbooks</a></li> --}}
        {{-- @endif --}}
    </ul>
</li>
@if(
// ONLY SHOW NEW CLAIMS FOR ADMINS, VIP, AND DEMO BUSINESS
    (auth()->user()->role_type == 'office_user' && in_array(auth()->user()->role->chain_id, [1, 51]))
    || auth()->user()->role_type == 'admin'
)
<li>
    <a class="has-arrow" href="javascript:void(0)" aria-expanded="false">
        <i class="fa fa-file-text" style="margin-left: 3px; margin-right: -3px;"></i><span class="hide-menu">Claims</span>
    </a>
    <ul aria-expanded="false" class="collapse">
        <li><a href="{{ route('business.claims-queue') }}">Claims Queue</a></li>
        <li><a href="{{ route('business.claim-remits.index') }}">Remits</a></li>
        <li><a href="{{ route('business.reports.claims.ar-aging') }}">AR Aging Report</a></li>
        <li><a href="{{ route('business.reports.claims.transmissions') }}">Transmissions Report</a></li>
        <li><a href="{{ route('business.reports.claims.remit-application') }}">Remit Application Report</a></li>
    </ul>
</li>
@endif

@if ( Gate::allows( 'view-reports' ) )
<li>
    <a href="{{ route('business.reports.index') }}" ><i class="fa fa-bar-chart"></i><span class="hide-menu">Reports</span></a>
</li>
@endif

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
        <li><a href="{{ route('business.communication.sms-other-replies') }}">View Unsorted replies</a></li>
    </ul>
</li>
<li>
    <a class="has-arrow" href="javascript:void(0)" aria-expanded="false"><i class="fa fa-cart-arrow-down"></i><span class="hide-menu">Referral Sources </span></a>
    <ul aria-expanded="false" class="collapse">
        <li><a href="{{ route('business.referral-sources.index') }}?type=client">Client Sources</a></li>
        <li><a href="{{ route('business.referral-sources.index') }}?type=caregiver">Caregiver Sources</a></li>
    </ul>
</li>
<li>
    <a class="" href="{{ route('knowledge.base') }}" aria-expanded="false">
        <i class="fa fa-lightbulb-o"></i><span class="hide-menu">Knowledge Base</span>
    </a>
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