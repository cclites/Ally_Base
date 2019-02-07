<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="viewport-fit=cover, initial-scale=1, maximum-scale=1, user-scalable=no, width=device-width" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <title>@yield('title', 'Login') | {{ config('app.name', 'Ally Management System') }}</title>
    @include('layouts.partials.head')
    @stack('head')
    <style>
        .page-wrapper {
            margin-left: 0 !important;
        }
        .footer {
            left: 0 !important;
            text-align: center;
        }
    </style>
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
<div id="main-wrapper">
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

            @yield('content')

        </div>
    </div>
    <!-- ============================================================== -->
    <!-- End Page wrapper  -->
    <!-- ============================================================== -->
</div>
<!-- ============================================================== -->
<!-- End Wrapper -->
<!-- ============================================================== -->

@include('layouts.partials.scripts')

<!-- Page Level JavaScript -->
@stack('scripts')
</body>

</html>
