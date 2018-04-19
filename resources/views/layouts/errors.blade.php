<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- Tell the browser to be responsive to screen width -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>@yield('title', 'Dashboard') | {{ config('app.name', 'Ally Management System') }}</title>
    @include('layouts.partials.head')

    <style>
        html, body {
            height:100%;
        }

        body {
            background: #00aeef;
        }

        body, h1, h2, h3, h4, h5, h6, p {
            color: #fff;
        }

        #logo {
            margin-top: 10%;
            max-height: 150px;
        }

    </style>

    @stack('head')
</head>

<body>

    <div class="row">
        <div class="col text-center">
            <img src="/images/AllyLogo.png" id="logo" />
        </div>
    </div>

    <div class="row with-padding-top">
        <div class="col text-center">
            @yield('content')
        </div>
    </div>

</body>
@include('layouts.partials.scripts')

<!-- Page Level JavaScript -->
@stack('scripts')
</body>

</html>