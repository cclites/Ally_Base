@extends('layouts.print')

@section('content')
    <style>
        .mt {
            margin-top: 1.2rem;
        }

        .img-signature > img {
            max-width: 400px;
            width: 400px;
        }
    </style>

    {!!  $terms !!}

    @include('business.clients.payment_details')

@endsection
