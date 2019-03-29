@extends('layouts.guest')

@section('title', 'Thank You!')

@section('content')
<b-row>
    <b-col>
        <b-card class="text-center">
            @if ($confirmed)
                <span>Your shifts have been confirmed.</span>
            @else
                <span>These shifts have already been confirmed.</span>
            @endif
        </b-card>

        @if (count($unconfirmedShifts))
            <b-card header="There are additional shifts to confirm">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th scope="col">Date</th>
                            <th scope="col">Caregiver</th>
                            <th scope="col">Hours</th>
                            <th scope="col">Rate</th>
                            <th scope="col">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($unconfirmedShifts as $shift)
                            <tr>
                                <th scope="row">{{ $shift->date->format('m/d/Y') }}</th>
                                <td>{{ $shift->caregiver }}</td>
                                <td>{{ $shift->hours }}</td>
                                <td>${{ is_numeric($shift->rate) ? number_format($shift->rate, 2) : '---' }}</td>
                                <td>${{ number_format($shift->total, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                
                <div class="m-5 text-center"><h3>Total Pending Charge: ${{ number_format($total, 2) }}</h3></div>
                <div class="text-center">
                    <b-btn variant="success" size="lg" href="/confirm-shifts/all/{{ $token->token }}">Confirm These Shifts Now</b-btn>
                </div>
            </b-card>
        @endif
    </b-col>
</b-row>
@endsection
