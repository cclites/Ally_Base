<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="viewport-fit=cover, initial-scale=1, maximum-scale=1, user-scalable=no, width=device-width" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <title>@yield('title', 'Dashboard') | {{ config('app.name', 'Ally Management System') }}</title>
    @include('layouts.partials.head')

    @if(is_mobile_app())
        <style>
            #header-desktop { display: none; }
            #header-mobile { display: block; }
            #logo-text { display: none; }
        </style>
    @endif

    <script>window.fcsKey = '{{ config('services.fullcalendar.key') }}';</script>

    @stack('head')
</head>

<body class="fix-header fix-sidebar card-no-border">
<!-- ============================================================== -->
<!-- Preloader - style you can find in spinners.css -->
<!-- ============================================================== -->
<div class="preloader">
    <svg class="circular" viewBox="25 25 50 50">
        <circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="2" stroke-miterlimit="10" /> </svg>
</div>
<!-- Alert Messages - see alerts.js and Message.vue -->
<div id="alerts">
    <message v-for="message in messages" :msg="message" :key="message.id"></message>
</div>
<!-- ============================================================== -->
<!-- Main wrapper - style you can find in pages.scss -->
<!-- ============================================================== -->
@if(auth()->check() && auth()->user()->isImpersonating())
    <div id="impersonator-bar">
        <div class="row">
            <div class="col-sm-8">
                You are currently impersonating {{ auth()->user()->name() }}.
            </div>
            <div class="col-sm-4 text-right">
                <a href="{{ route('impersonate.stop') }}" class="btn btn-small btn-secondary" style="padding: 2px 5px;">Stop Impersonating</a>
                @if(in_array(auth()->user()->role_type, ['client', 'caregiver']))
                    <a href="{{ route('impersonate.business', ['business' => collect(auth()->user()->getBusinessIds())->first() ]) }}" class="btn btn-small btn-secondary" style="padding: 2px 5px;">Back to Business</a>
                @endif
            </div>
        </div>
    </div>
@endif
<div id="main-wrapper">
    @include('layouts.partials.header_desktop')
    @include('layouts.partials.header_mobile')

    <!-- ============================================================== -->
    <!-- Left Sidebar - style you can find in sidebar.scss  -->
    <!-- ============================================================== -->
    <aside class="left-sidebar"
        @if(is_mobile_app() && is_ios())
            style="padding-top: 100px;"
        @endif
    >
        <!-- Sidebar scroll-->
        <div class="scroll-sidebar">
            <!-- Sidebar navigation-->
            <nav class="sidebar-nav">
                <ul id="sidebarnav">
                    @include('menu.menu')
                </ul>
            </nav>
            <!-- End Sidebar navigation -->
        </div>
        <!-- End Sidebar scroll-->
        <!-- Bottom points-->
        <div class="sidebar-footer">
            <!-- item--><!--  <a href="" class="link" data-toggle="tooltip" title="Settings"><i class="fa fa-gears"></i></a> -->
            <!-- item--><!--  <a href="" class="link" data-toggle="tooltip" title="Email"><i class="mdi mdi-gmail"></i></a> -->
            <!-- item--><a href="{{ url('/logout') }}"
                           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                Logout
            </a> </div>
        <!-- End Bottom points-->
    </aside>
    <!-- ============================================================== -->
    <!-- End Left Sidebar - style you can find in sidebar.scss  -->
    <!-- ============================================================== -->
    <!-- ============================================================== -->
    <!-- Page wrapper  -->
    <!-- ============================================================== -->
    <div class="page-wrapper"
        @if(is_mobile_app() && is_ios())
            style="padding-top: 100px;"
        @endif
    >
        <!-- ============================================================== -->
        <!-- Container fluid  -->
        <!-- ============================================================== -->
        <div class="container-fluid">
            <!-- ============================================================== -->
            <!-- Bread crumb and right sidebar toggle -->
            <!-- ============================================================== -->

            <div class="row page-titles">
                <div class="col-md-6 col-lg-8 align-self-center">
                    @hasSection('avatar')
                        <div class="row">
                            <div class="col-lg-1 mt-2">
                                @yield('avatar')
                            </div>
                            <div class="col-lg-11">
                                @include('layouts.partials.breadcrumbs')
                            </div>
                        </div>
                    @else
                        @include('layouts.partials.breadcrumbs')
                    @endif
                </div>
                <div class="col-md-6 col-lg-4 hidden-xs-down pt-3">
                    @if (in_array(auth()->user()->role_type, ['admin', 'office_user']))
                        <quick-search></quick-search>
                    @endif
                    {{--<div class="d-flex m-t-10 justify-content-end">--}}
                        {{--<div class="d-flex m-r-20 m-l-10 hidden-md-down">--}}
                            {{--<div class="chart-text m-r-10">--}}
                                {{--<h6 class="m-b-0"><small>THIS MONTH</small></h6>--}}
                                {{--<h4 class="m-t-0 text-info">$58,356</h4></div>--}}
                            {{--<div class="spark-chart">--}}
                                {{--<div id="monthchart"></div>--}}
                            {{--</div>--}}
                        {{--</div>--}}
                        {{--<div class="d-flex m-r-20 m-l-10 hidden-md-down">--}}
                            {{--<div class="chart-text m-r-10">--}}
                                {{--<h6 class="m-b-0"><small>LAST MONTH</small></h6>--}}
                                {{--<h4 class="m-t-0 text-primary">$48,356</h4></div>--}}
                            {{--<div class="spark-chart">--}}
                                {{--<div id="lastmonthchart"></div>--}}
                            {{--</div>--}}
                        {{--</div>--}}
                    {{--</div>--}}
                </div>
            </div>
            <!-- ============================================================== -->
            <!-- End Bread crumb and right sidebar toggle -->
            <!-- ============================================================== -->
            <!-- ============================================================== -->
            <!-- Start Page Content -->
            <!-- ============================================================== -->
            @include('layouts.partials.messages')
            @yield('content')
            <!-- ============================================================== -->
            <!-- End Page Content -->
            <!-- ============================================================== -->
        </div>
        <!-- ============================================================== -->
        <!-- End Container fluid  -->
        <!-- ============================================================== -->
        @include('layouts.partials.footer')
    </div>
    <!-- ============================================================== -->
    <!-- End Page wrapper  -->
    <!-- ============================================================== -->

    @if( is_caregiver() && Auth::user()->can( 'view-open-shifts' ) )

        <open-shifts role_type="{{ auth()->user()->role_type }}"></open-shifts>
    @endif

</div>
<!-- ============================================================== -->
<!-- End Wrapper -->
<!-- ============================================================== -->

<form id="logout-form" action="{{ url('/logout') }}" method="POST" style="display: none;">
    {{ csrf_field() }}
</form>

@include('layouts.partials.scripts')

<!-- Page Level JavaScript -->
@stack('scripts')
</body>

</html>
