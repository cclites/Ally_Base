@extends('layouts.guest')

@section('title', 'Check My Time')

@section('content')
    <div class="container text-center">
        <h5>
            Your computer's time is set to: <span id="time" style="font-weight: bold"></span><br /><br />
            Your computer's user agent is: <span id="userAgent" style="font-weight: bold"></span>
        </h5>
    </div>
@endsection

@push('scripts')
    <script>
        $('#time').text(moment().toDate());
        $('#userAgent').text(window.navigator.userAgent);
    </script>
@endpush
