
<meta name="csrf-token" content="{{ csrf_token() }}">

<!-- Favicon icon -->
<link rel="icon" type="image/png" sizes="16x16" href="/ico/favicon.ico">

<!-- Fonts -->
<link href="https://fonts.googleapis.com/css?family=Homemade+Apple" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" crossorigin="anonymous">

<!-- App CSS -->
<link href="{{ asset(mix('css/style.css')) }}" rel="stylesheet" />
<link href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.9.0/fullcalendar.print.css" rel="stylesheet" media="print" />

<!--[if lt IE 9]>
<script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
<script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
<![endif]-->

<script>
    try {
        window.gmapsKey = '{{ config('services.gmaps.key') }}';
        @if (empty(auth()->user()))
            window.AuthUser = {};
        @else

            window.AuthUser = JSON.parse('{!! str_replace( "'", "\'", str_replace( '"', '\"',
            json_encode( auth()->user()->withImpersonationDetails(), JSON_UNESCAPED_UNICODE )
            ) ) !!}');
        @endif
        window.OfficeUserSettings = JSON.parse('{!! str_replace('"', '\"', json_encode(
            (new \App\Users\SettingsRepository)->getOfficeUserSettings(auth()->user()), JSON_UNESCAPED_UNICODE)
        ) !!}');
    }
    catch(e) { console.log(e); }
</script>
