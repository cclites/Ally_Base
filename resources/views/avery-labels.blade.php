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
            height: 100%;
            width: 100%;
        }
        table {
            border-collapse: collapse;
            border-spacing: 0;
            width: 100%!important;
        }

        table td {
            width: 33.3%;
            /* background-color: red;
            border: 3px solid black; */
            padding: 15px 5px 15px;
            font-family: Arial, Helvetica;
            line-height: 1.25em;
            font-size: 14px;
            height: 104px;
            text-align: center;
            vertical-align: middle;
        }

        table tr td:first-child {

            padding-right: 55px;
            padding-left: 0px;
        }

        table tr td:last-child {

            padding-left: 55px;
            padding-right: 0px;
        }
    </style>
@endpush

@section('content')

    <table cellspacing="0" cellpadding="0" border="1">

        @foreach( $users as $row => $cols )

            <tr>

                @for( $col = 0; $col < count( $cols ); $col++ )

                    <td @if( $row === 0 ) style="padding-top:55px" @endif @if( $row % 10 >= 4 ) style="padding-top: 10px;padding-bottom: 0px" @endif>

                        {{ $cols[ $col ][ 'name' ] }}

                        @if( $cols[ $col ][ 'address' ][ 'address1' ] )

                            <div>{{ $cols[ $col ][ 'address' ][ 'address1' ] }}</div>
                        @else

                            <div>{{ '-- NO ADDRESS LINE 1 --' }}</div>
                        @endif

                        @if( $cols[ $col ][ 'address' ][ 'address2' ] )

                            <div>{{ $cols[ $col ][ 'address' ][ 'address2' ] }}</div>
                        @else

                            <br />
                        @endif

                        @if( $cols[ $col ][ 'address' ][ 'city' ] && $cols[ $col ][ 'address' ][ 'state' ] )

                            <span>{{ $cols[ $col ][ 'address' ][ 'city' ] }}</span>,
                            <span>{{ $cols[ $col ][ 'address' ][ 'state' ] }}</span>
                        @elseif($cols[ $col ][ 'address' ][ 'city' ])

                            {{ $cols[ $col ][ 'address' ][ 'city' ] }}
                        @elseif( $cols[ $col ][ 'address' ][ 'state' ])

                            {{ $cols[ $col ][ 'address' ][ 'state' ] }}
                        @endif
                        <span>{{ $cols[ $col ][ 'address' ][ 'zip' ] }}</span>
                    </td>
                @endfor
            </tr>
        @endforeach
    </table>
@endsection