<li>
    <a class="franchisor-navlink" href="/" aria-expanded="false"><i class="mdi mdi-gauge"></i><span class="hide-menu">Dashboard </span></a>
</li>
<li>
    <a class="franchisor-navlink" href="{{ route('business.franchisees') }}" aria-expanded="false"><i class="fa fa-building"></i><span class="hide-menu">Offices </span></a>
</li>
@if ( Gate::allows( 'view-reports' ) )
<li>
    <a class="franchisor-navlink" href="{{ route('business.franchise.reports') }}" aria-expanded="false"><i class="fa fa-book"></i><span class="hide-menu">Reports </span></a>
</li>
@endif
<li>
    <a class="franchisor-navlink" href="{{ route('business.franchise.payments') }}" aria-expanded="false"><i class="fa fa-money"></i><span class="hide-menu">Payments </span></a>
</li>
