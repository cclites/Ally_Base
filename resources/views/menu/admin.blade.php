<li>
    <a class="has-arrow" href="/home" aria-expanded="false"><i class="mdi mdi-gauge"></i><span class="hide-menu">Dashboard </span></a>
</li>
<li> <a class="has-arrow" href="{{ route('business.clients.index') }}" aria-expanded="false"><i class="fa fa-users"></i><span class="hide-menu">Businesses</span></a>
    <ul aria-expanded="false" class="collapse">
        <li><a href="{{ route('admin.businesses.index') }}">Business List</a></li>
        <li><a href="{{ route('admin.businesses.create') }}">Add Business</a></li>
    </ul>
</li>
