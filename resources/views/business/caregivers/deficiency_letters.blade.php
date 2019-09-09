@extends( 'layouts.print' )

@section( 'title', 'Deficiency Letters' )

@push( 'head' )

    <style>

        .expirations-table {

            width: 100%;
        }

        .expirations-table td, .expirations-table th {

            border: 1px solid #dddddd;
            text-align: left;
            padding: 8px;
        }

        .expirations-table tr:nth-child(even) {

            background-color: #dddddd;
        }
</style>
@endpush

@section( 'content' )

    @foreach( $pages as $deficiencyLetter )

        <div class="page">


            @include( 'invoices.partials.address', [ 'address' => $deficiencyLetter->caregiver->address ] )

            <p style="margin-top:45px; margin-bottom: 35px">Dear {{ explode( ' ', $deficiencyLetter->caregiver->name )[ 0 ] }}</p>

            <p style="margin:20px 0px">{{ $intro }}</p>

            <p style="margin:20px 0px">{{ $middle }}</p>

            <div style="margin:25px 0px">

                <table class="expirations-table">

                    <caption>Expirations Table</caption>
                    <tr>

                        <th>Expiring Item</th>
                        <th>Expiring Date</th>
                    </tr>
                    @foreach ( $deficiencyLetter as $expiration )

                         <tr>

                            <td>{{ $expiration[ 'name' ] }}</td>
                            <td>{{ Carbon\Carbon::parse( $expiration[ 'expiration_date' ] )->format( 'm/d/Y' ) }}</td>
                        </tr>
                    @endforeach
                </table>
            </div>

            <p style="margin:20px 0px">Audited on <strong>{{ $today }}</strong>. Includes items expiring on <strong>{{ $start_date }}</strong> through <strong>{{ $end_date }}</strong>.</p>

            <p style="margin:20px 0px">{{ $outro }}</p>

            <p style="margin:20px 0px">{{ $final_words }}</p>

            <p style="margin:20px 0px">Sincerely, <br/> {{ $farewell }}</p>
        </div>
    @endforeach
@endsection