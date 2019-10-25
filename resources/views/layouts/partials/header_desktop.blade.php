<header id="header-desktop"
        @if (config('app.env') == 'staging')
        style="background:#ce4747;"
        @endif
        class="topbar">
    <nav class="navbar top-navbar navbar-expand-md navbar-light">

        @include('layouts.partials.logo')

        <div class="navbar-collapse">
            <!-- ============================================================== -->
            <!-- toggle and nav items -->
            <!-- ============================================================== -->
            <ul class="navbar-nav mr-auto mt-md-0">
                <li class="nav-item"> <a class="nav-link nav-toggler hidden-md-up text-muted" href="javascript:void(0)"><i class="mdi mdi-menu"></i></a> </li>
                <li class="nav-item"> <a class="nav-link sidebartoggler hidden-sm-down text-muted" href="javascript:void(0)"><i class="mdi mdi-menu"></i></a> </li>
            </ul>
            <!-- ============================================================== -->
            <!-- User profile and search -->
            <!-- ============================================================== -->
            <ul class="navbar-nav my-lg-0">
                <!-- ============================================================== -->
                <!-- Exceptions -->
                <!-- ============================================================== -->
                @if(is_office_user())
                    <system-notifications-icon></system-notifications-icon>
                @endif
                <!-- ============================================================== -->
                <!-- Open Shifts Feature -->
                <!-- ============================================================== -->
                {{-- @if(Auth::check() && in_array(Auth::user()->role_type, ['office_user', 'caregiver']) && !in_array( $active_business->open_shifts_setting, [ App\Business::OPEN_SHIFTS_DISABLED ] ))
                    <a class="nav-link dropdown-toggle text-muted" href="" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="mdi mdi-account-circle"></i></a>
                @endif --}}
                @if( Auth::user()->can( 'view-open-shifts' ) )
                    {{ 'hey lol' }}
                @endif
                <!-- ============================================================== -->
                <!-- Tasks -->
                <!-- ============================================================== -->
                @if( is_office_user() || is_caregiver() )
                    <tasks-icon role="{{ Auth::user()->role_type }}"></tasks-icon>
                @endif
                <!-- ============================================================== -->
                <!-- Active Business -->
                <!-- ============================================================== -->
                @if(is_office_user() || is_admin())
                    @include('layouts.partials.active_business')
                @endif
                <!-- ============================================================== -->
                <!-- Profile -->
                <!-- ============================================================== -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-muted" href="" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="mdi mdi-account-circle"></i></a>
                    <div class="dropdown-menu dropdown-menu-right scale-up">
                        <ul class="dropdown-user">
                            <li>
                                <div class="dw-user-box">
                                    <div class="u-text">
                                        <h4>{{ Auth::check() ? Auth::user()->name() : 'Guest' }}</h4>
                                        <p class="text-muted">{{ Auth::check() ? Auth::user()->email : 'Not logged in' }}</p></div>
                                </div>
                            </li>
                            @if(! is_admin_now())
                            <li role="separator" class="divider"></li>
                            <li><a href="{{ route('profile') }}"><i class="fa fa-user"></i> My Profile</a></li>
                            @endif
                            <li role="separator" class="divider"></li>
                            <li><a href="{{ url('/logout') }}"
                                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    <i class="fa fa-power-off"></i> Logout
                                </a></li>
                        </ul>
                    </div>
                </li>
            </ul>
        </div>
    </nav>
</header>