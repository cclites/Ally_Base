@extends('layouts.print')

@section('title', "Claim")

@push('head')
    <style>

        html, body, div, table, tbody, tfoot, thead, tr, th, td {
            margin: 0;
            padding: 0;
            border: 0;
            font-size: 100%;
            font: inherit;
        }
        body {
            line-height: 1;
            height: 100%
        }
        table {
            border-collapse: collapse;
            border-spacing: 0;
        }

        body {
            margin-top: 45px;
            font-family: Arial, Helvetica;
            font-size: 10px;
        }
        table {
            width: 100%!important;
        }
        table td {
            width: 33.3%;
            padding: 15px 20px;
            font-family: Arial, Helvetica;
            font-size: 18px;
            height: 96px;
            text-align: center;
            vertical-align: middle;
        }
    </style>
@endpush

@section('content')

    <table cellspacing="0" cellpadding="0" border="1">

        @foreach( $users as $row => $cols )

            <tr>

                @for( $col = 0; $col < count( $cols ); $col++ )

                    <td>

                        {{ $cols[ $col ][ 'name' ] }}

                        <div>{{ $cols[ $col ][ 'address' ][ 'address1' ] }}</div>
                        @if($cols[ $col ][ 'address' ][ 'address2' ])

                            <div>{{ $cols[ $col ][ 'address' ][ 'address2' ] }}</div>
                        @endif
                        @if($cols[ $col ][ 'address' ][ 'city' ] && $cols[ $col ][ 'address' ][ 'state' ])

                            <span>{{ $cols[ $col ][ 'address' ][ 'city' ] }}</span>,
                            <span>{{ $cols[ $col ][ 'address' ][ 'state' ] }}</span>
                        @elseif($cols[ $col ][ 'address' ][ 'city' ])

                            {{ $cols[ $col ][ 'address' ][ 'city' ] }}
                        @elseif($cols[ $col ][ 'address' ][ 'state' ])

                            {{ $cols[ $col ][ 'address' ][ 'state' ] }}
                        @endif
                        <span>{{ $cols[ $col ][ 'address' ][ 'zip' ] }}</span>
                    </td>
                @endfor
            </tr>
        @endforeach
    </table>
@endsection