@extends('layouts.print')

@section('title', "Claim")

@push('head')
    <style>

        html, body {

            margin: 0;
            padding: 0;
            border: 0;
            font-size: 100%;
            font: inherit;
        }

        .full-page-table {

            display: block;
            position: relative;

            page-break-after: always;
        }

        .table-row {

            display: block;
            position: relative;

            height: 125px;
            width: 100%;
        }

        .table-column {

            position: relative;
            display: inline-block;
            width: 33%;
            height: 100%;

            padding: 5px 5px 15px;
            font-family: Arial, Helvetica;
            font-size: 14px;

            text-align: center;
            padding-top: 20px;
        }

        .error-field {

            color: red !important;
        }
    </style>
@endpush

@section('content')

    @foreach( $pages as $page )

        <div class="full-page-table" style="left: {{ $leftmargin . 'px' }}; top: {{ $topmargin . 'px' }}">

            @foreach( $page as $row => $cols )

                <div class="table-row">

                    @foreach( $cols as $col => $user )

                        <div class="table-column"
                            @if( strlen( $user[ 'address' ][ 'address1' ] ) > 30 ) class="error-field" @endif
                            @if( strlen( $user[ 'address' ][ 'address2' ] ) > 30 ) class="error-field" @endif
                            @if( strlen( $user[ 'address' ][ 'city' ] ) > 30 ) class="error-field" @endif
                        >

                            {{ $user[ 'name' ] }}

                            @if( $user[ 'address' ][ 'address1' ] )

                                <div>{{ substr( $user[ 'address' ][ 'address1' ], 0, 30 ) . ( strlen( $user[ 'address' ][ 'address1' ] ) > 30 ? '*' : '' ) }}</div>
                            @else

                                <br />
                            @endif

                            @if( $user[ 'address' ][ 'address2' ] )

                                <div>{{ substr( $user[ 'address' ][ 'address2' ], 0, 30 ) . ( strlen( $user[ 'address' ][ 'address2' ] ) > 30 ? '*' : '' ) }}</div>
                            @else

                                <br />
                            @endif

                            @if( $user[ 'address' ][ 'city' ] && $user[ 'address' ][ 'state' ] )

                                <span>{{ $user[ 'address' ][ 'city' ] }}</span>,
                                <span>{{ $user[ 'address' ][ 'state' ] }}</span>
                            @elseif($user[ 'address' ][ 'city' ])

                                {{ $user[ 'address' ][ 'city' ] }}
                            @elseif( $user[ 'address' ][ 'state' ])

                                {{ $user[ 'address' ][ 'state' ] }}
                            @endif
                            <span>{{ $user[ 'address' ][ 'zip' ] }}</span>
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
    @endforeach
@endsection