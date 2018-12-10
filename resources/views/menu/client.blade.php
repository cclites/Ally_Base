@if(auth()->user()->role->client_type == 'LTCI')
    <li>
        <a href="/shift-history" aria-expanded="false">
            <i class="mdi mdi-history"></i><span class="hide-menu">LTC Shift Approval</span>
        </a>
    </li>
@endif
@if(auth()->user()->role->business->allow_client_confirmations)
    <li>
        <a href="/unconfirmed-shifts" aria-expanded="false">
            <i class="mdi mdi-history"></i><span class="hide-menu">Pending Shifts</span>
        </a>
    </li>
@endif
<li>
    <a href="/payment-history" aria-expanded="false">
        <i class="mdi mdi-credit-card"></i><span class="hide-menu">Payment History</span>
    </a>
</li>
<li>
    <a href="/profile" aria-expanded="false">
        <i class="mdi mdi-account-circle"></i><span class="hide-menu">My Information</span>
    </a>
</li>
<li>
    <a href="{{ route('clients.caregivers') }}" aria-expanded="false">
        <i class="fa fa-users"></i><span class="hide-menu">My Caregivers</span>
    </a>
</li>
{{--<li>--}}
    {{--<a class="" href="{{ route('knowledge.base') }}" aria-expanded="false">--}}
        {{--<i class="fa fa-lightbulb-o"></i><span class="hide-menu">Knowledge Base</span>--}}
    {{--</a>--}}
{{--</li>--}}