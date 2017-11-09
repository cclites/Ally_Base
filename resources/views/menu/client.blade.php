<li>
    <a href="/home" aria-expanded="false">
        <i class="mdi mdi-gauge"></i><span class="hide-menu">Dashboard</span>
    </a>
</li>
@if(auth()->user()->role->client_type == 'LTCI')
    <li>
        <a href="/shift-history" aria-expanded="false">
            <i class="mdi mdi-history"></i><span class="hide-menu">LTC Shift Approval</span>
        </a>
    </li>
@endif
<li>
    <a href="/payment-history" aria-expanded="false">
        <i class="mdi mdi-gauge"></i><span class="hide-menu">Payment History</span>
    </a>
</li>
