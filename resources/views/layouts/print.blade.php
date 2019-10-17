<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- Tell the browser to be responsive to screen width -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>@yield('title', 'Login') | {{ config('app.name', 'Ally Management System') }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Favicon icon -->
    @if(isset($render) && $render == 'html')
        <link rel="icon" type="image/png" sizes="16x16" href="/ico/favicon.ico">

        <link rel="stylesheet" href="{{ asset(mix('print/bootstrap-3.3.7-dist/css/bootstrap.min.css')) }}">
        <link href="{{ asset(mix('print/Homemade+Apple.css')) }}" rel="stylesheet" />
        <link rel="stylesheet" href="{{ asset(mix('print/font-awesome-4.7.0/css/font-awesome.css')) }}">
        <link rel="stylesheet" href="{{ asset(mix('print/full-calendar.3.5.1.min.css')) }}" />
        <link href="{{ asset(mix('css/style.css')) }}" rel="stylesheet" />
        <!--[if lt IE 9]>
        <script src="{{ asset('print/html5shiv.3.7.0.js') }}"></script>
        <script src="{{ asset('print/respond.js.1.4.2.js') }}"></script>
        <![endif]-->
    @else
        <link rel="stylesheet" href="{{ public_path('print/bootstrap-3.3.7-dist/css/bootstrap.min.css') }}">
        <link href="{{ public_path('print/Homemade+Apple.css') }}" rel="stylesheet" />
        <link rel="stylesheet" href="{{ public_path('print/font-awesome-4.7.0/css/font-awesome.css') }}">
        <link rel="stylesheet" href="{{ public_path('print/full-calendar.3.5.1.min.css') }}" />
        <link href="{{ public_path('print/css/style.css') }}" rel="stylesheet" />
    @endif
    <!-- Bootstrap  -->
{{--    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">--}}

    <!-- Fonts -->
{{--    <link href="https://fonts.googleapis.com/css?family=Homemade+Apple" rel="stylesheet">--}}
{{--    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">--}}

    <!-- Full Calendar -->
{{--    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.5.1/fullcalendar.min.css" />--}}

    <!-- App CSS -->
{{--    <link href="{{ asset(mix('css/style.css')) }}" rel="stylesheet" />--}}

{{--    <!--[if lt IE 9]>--}}
{{--    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>--}}
{{--    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>--}}
{{--    <![endif]-->--}}
    <style>
        body {
            margin: 10px;
        }
        .page-wrapper {
            margin-left: 0 !important;
        }
        .footer {
            left: 0 !important;
            text-align: center;
        }
        div.page
        {
            margin: 2mm;
            page-break-after: always;
            page-break-inside: avoid;
        }
        div.page:last-of-type
        {
            page-break-after: avoid;
        }

        table { page-break-inside:auto }
        tr    { page-break-inside:avoid; page-break-after:auto }
        thead { display:table-header-group }
        tfoot { display:table-footer-group }

        .print-controls {
            padding: 20px 10px;
        }
        .print-content, 
        .print-content h4,
        .print-content th,
        .print-content td {
            color: black;
        }
        @media print {
            .print-controls {
                display: none;
            }
        }
    </style>

    @stack('head')
</head>

<body>

<!-- ============================================================== -->
<!-- Preloader - style you can find in spinners.css -->
<!-- ============================================================== -->
<!-- ============================================================== -->
<!-- Main wrapper - style you can find in pages.scss -->
<!-- ============================================================== -->

@yield('content')

<!-- ============================================================== -->
<!-- End Wrapper -->
<!-- ============================================================== -->
</body>

</html>