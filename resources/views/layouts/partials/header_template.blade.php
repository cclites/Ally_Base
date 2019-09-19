<header
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
                <!-- This is  -->
                <li class="nav-item"> <a class="nav-link nav-toggler hidden-md-up text-muted" href="javascript:void(0)"><i class="mdi mdi-menu"></i></a> </li>
                <li class="nav-item"> <a class="nav-link sidebartoggler hidden-sm-down text-muted" href="javascript:void(0)"><i class="mdi mdi-menu"></i></a> </li>
                <!-- ============================================================== -->
                <!-- Search -->
                <!-- ============================================================== -->
            {{--<li class="nav-item hidden-sm-down search-box"> <a class="nav-link hidden-sm-down text-muted" href="javascript:void(0)"><i class="fa fa-search"></i></a>--}}
            {{--<form class="app-search">--}}
            {{--<input type="text" class="form-control" placeholder="Search & enter"> <a class="srh-btn"><i class="fa fa-times"></i></a> </form>--}}
            {{--</li>--}}
            <!-- ============================================================== -->
                <!-- Messages -->
                <!-- ============================================================== -->
            {{--<li class="nav-item dropdown mega-dropdown"> <a class="nav-link dropdown-toggle text-muted" href="" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="mdi mdi-view-grid"></i></a>--}}
            {{--<div class="dropdown-menu scale-up-left">--}}
            {{--<ul class="mega-dropdown-menu row">--}}
            {{--<li class="col-lg-3 col-xlg-2 m-b-30">--}}
            {{--<h4 class="m-b-20">CAROUSEL</h4>--}}
            {{--<!-- CAROUSEL -->--}}
            {{--<div id="carouselExampleControls" class="carousel slide" data-ride="carousel">--}}
            {{--<div class="carousel-inner" role="listbox">--}}
            {{--<div class="carousel-item active">--}}
            {{--<div class="container"> <img class="d-block img-fluid" src="/demo/assets/images/big/img1.jpg" alt="First slide"></div>--}}
            {{--</div>--}}
            {{--<div class="carousel-item">--}}
            {{--<div class="container"><img class="d-block img-fluid" src="/demo/assets/images/big/img2.jpg" alt="Second slide"></div>--}}
            {{--</div>--}}
            {{--<div class="carousel-item">--}}
            {{--<div class="container"><img class="d-block img-fluid" src="/demo/assets/images/big/img3.jpg" alt="Third slide"></div>--}}
            {{--</div>--}}
            {{--</div>--}}
            {{--<a class="carousel-control-prev" href="#carouselExampleControls" role="button" data-slide="prev"> <span class="carousel-control-prev-icon" aria-hidden="true"></span> <span class="sr-only">Previous</span> </a>--}}
            {{--<a class="carousel-control-next" href="#carouselExampleControls" role="button" data-slide="next"> <span class="carousel-control-next-icon" aria-hidden="true"></span> <span class="sr-only">Next</span> </a>--}}
            {{--</div>--}}
            {{--<!-- End CAROUSEL -->--}}
            {{--</li>--}}
            {{--<li class="col-lg-3 m-b-30">--}}
            {{--<h4 class="m-b-20">ACCORDION</h4>--}}
            {{--<!-- Accordian -->--}}
            {{--<div id="accordion" class="nav-accordion" role="tablist" aria-multiselectable="true">--}}
            {{--<div class="card">--}}
            {{--<div class="card-header" role="tab" id="headingOne">--}}
            {{--<h5 class="mb-0">--}}
            {{--<a data-toggle="collapse" data-parent="#accordion" href="#collapseOne" aria-expanded="true" aria-controls="collapseOne">--}}
            {{--Collapsible Group Item #1--}}
            {{--</a>--}}
            {{--</h5> </div>--}}
            {{--<div id="collapseOne" class="collapse show" role="tabpanel" aria-labelledby="headingOne">--}}
            {{--<div class="card-body"> Anim pariatur cliche reprehenderit, enim eiusmod high. </div>--}}
            {{--</div>--}}
            {{--</div>--}}
            {{--<div class="card">--}}
            {{--<div class="card-header" role="tab" id="headingTwo">--}}
            {{--<h5 class="mb-0">--}}
            {{--<a class="collapsed" data-toggle="collapse" data-parent="#accordion" href="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">--}}
            {{--Collapsible Group Item #2--}}
            {{--</a>--}}
            {{--</h5> </div>--}}
            {{--<div id="collapseTwo" class="collapse" role="tabpanel" aria-labelledby="headingTwo">--}}
            {{--<div class="card-body"> Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. </div>--}}
            {{--</div>--}}
            {{--</div>--}}
            {{--<div class="card">--}}
            {{--<div class="card-header" role="tab" id="headingThree">--}}
            {{--<h5 class="mb-0">--}}
            {{--<a class="collapsed" data-toggle="collapse" data-parent="#accordion" href="#collapseThree" aria-expanded="false" aria-controls="collapseThree">--}}
            {{--Collapsible Group Item #3--}}
            {{--</a>--}}
            {{--</h5> </div>--}}
            {{--<div id="collapseThree" class="collapse" role="tabpanel" aria-labelledby="headingThree">--}}
            {{--<div class="card-body"> Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. </div>--}}
            {{--</div>--}}
            {{--</div>--}}
            {{--</div>--}}
            {{--</li>--}}
            {{--<li class="col-lg-3  m-b-30">--}}
            {{--<h4 class="m-b-20">CONTACT US</h4>--}}
            {{--<!-- Contact -->--}}
            {{--<form>--}}
            {{--<div class="form-group">--}}
            {{--<input type="text" class="form-control" id="exampleInputname1" placeholder="Enter Name"> </div>--}}
            {{--<div class="form-group">--}}
            {{--<input type="email" class="form-control" placeholder="Enter email"> </div>--}}
            {{--<div class="form-group">--}}
            {{--<textarea class="form-control" id="exampleTextarea" rows="3" placeholder="Message"></textarea>--}}
            {{--</div>--}}
            {{--<button type="submit" class="btn btn-info">Submit</button>--}}
            {{--</form>--}}
            {{--</li>--}}
            {{--<li class="col-lg-3 col-xlg-4 m-b-30">--}}
            {{--<h4 class="m-b-20">List style</h4>--}}
            {{--<!-- List style -->--}}
            {{--<ul class="list-style-none">--}}
            {{--<li><a href="javascript:void(0)"><i class="fa fa-check text-success"></i> You can give link</a></li>--}}
            {{--<li><a href="javascript:void(0)"><i class="fa fa-check text-success"></i> Give link</a></li>--}}
            {{--<li><a href="javascript:void(0)"><i class="fa fa-check text-success"></i> Another Give link</a></li>--}}
            {{--<li><a href="javascript:void(0)"><i class="fa fa-check text-success"></i> Forth link</a></li>--}}
            {{--<li><a href="javascript:void(0)"><i class="fa fa-check text-success"></i> Another fifth link</a></li>--}}
            {{--</ul>--}}
            {{--</li>--}}
            {{--</ul>--}}
            {{--</div>--}}
            {{--</li>--}}
            <!-- ============================================================== -->
                <!-- End Messages -->
                <!-- ============================================================== -->
            </ul>
            <!-- ============================================================== -->
            <!-- User profile and search -->
            <!-- ============================================================== -->
            <ul class="navbar-nav my-lg-0">
                <!-- ============================================================== -->
                <!-- Active Business -->
                <!-- ============================================================== -->
                @if(is_office_user() || is_admin())
                    @include('layouts.partials.active_business')
                @endif
                <!-- ============================================================== -->
                <!-- Exceptions -->
                <!-- ============================================================== -->
                @if(is_office_user())
                    <system-notifications-icon></system-notifications-icon>
                @endif
                @if(Auth::check() && in_array(Auth::user()->role_type, ['office_user', 'caregiver']))
                    <tasks-icon role="{{ Auth::user()->role_type }}"></tasks-icon>
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
                            <li role="separator" class="divider"></li>
                            <li><a href="{{ route('profile') }}"><i class="fa fa-user"></i> My Profile</a></li>
                            <li role="separator" class="divider"></li>
                            <li><a href="{{ url('/logout') }}"
                                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    <i class="fa fa-power-off"></i> Logout
                                </a></li>
                        </ul>
                    </div>
                </li>
                <!-- ============================================================== -->
                <!-- Language -->
                <!-- ============================================================== -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-muted" href="" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> <i class="flag-icon flag-icon-us"></i></a>
                    <div class="dropdown-menu dropdown-menu-right scale-up"> <a class="dropdown-item" href="#"><i class="flag-icon flag-icon-in"></i> India</a> <a class="dropdown-item" href="#"><i class="flag-icon flag-icon-fr"></i> French</a> <a class="dropdown-item" href="#"><i class="flag-icon flag-icon-cn"></i> China</a> <a class="dropdown-item" href="#"><i class="flag-icon flag-icon-de"></i> Dutch</a> </div>
                </li>
            </ul>
        </div>
    </nav>
</header>