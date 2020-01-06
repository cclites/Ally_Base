<li class="nav-item dropdown hidden-xs-down">
    <a class="nav-link dropdown-toggle" href="" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <span class="active-business">
            @if($active_business && !is_admin_now())
                <span>{{ $active_business->name }}</span>
            @elseif (is_client() || is_caregiver())
                <span>{{ auth()->user()->name }}</span>
            @else
                <span>Admin Overview</span>
            @endif
            <i class="fa fa-caret-down"></i>
        </span>
    </a>
    <div class="dropdown-menu dropdown-menu-right scale-up">
        @if(is_admin())
            <ul class="dropdown-user" style="max-height: 480px; overflow-y: scroll">
                @foreach(App\Business::orderBy('name')->get() as $business)
                    <li>
                        <a href="{{ route('impersonate.business', [$business]) }}">{{ $business->name }}</a>
                    </li>
                @endforeach
            </ul>
        @else
            <ul class="dropdown-user">
                <li>
                    <a href="{{ route('business.settings.index') }}"><i class="fa fa-gears"></i> Settings</a>
                </li>
            </ul>
        @endif
    </div>
</li>
