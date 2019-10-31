@extends('layouts.print')

@section('title', "Claim")

@push('head')
    <style>

        .col-sm-6 {
            float: left;
            width: 50%;
        }

         body {
             color: #000;
         }

        .header-left,
        .footer-left {
            float: left;
            width: 65%;
            padding-left: 0;
        }

        .header-right,
        .footer-right {
            float: left;
            width: 35%;
            padding-right: 0;
        }

        .footer-left {
            padding-left: 2rem;
            padding-right: 4rem;
        }

        .header-right table tr td {
            padding-left: .5rem;
        }

        .shifts-table {
            margin-top: 2rem;
            font-size: 1.4rem;
        }

        .bg-info {
            color: white;
            background-color: #1e88e5!important;
        }

        .header-right-table {
            float: right;
        }

        .header-right-table td,
        .header-right-table th {
            text-align: left;
            padding: .5rem .75rem;
        }

        .print-header {
            margin: 0;
            background-color: #ccc;
            padding: 15px;
        }

        .logo img {
            max-height: 80px;
        }
    </style>
@endpush

@section('content')
        @include('layouts.partials.print_logo')

        @foreach( $categories as $key => $category )

            <h1>{{ $key }}</h1>
            @foreach( $category as $item )

                <div>

                    {{ $item->description }} :: {{ $item->text_code }}
                </div>
            @endforeach
        @endforeach
@endsection