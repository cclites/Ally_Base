@extends('layouts.app')

@section('title', 'Clock Out')

@section('content')
    <b-row>
        <b-col lg="6" class="text-center clock-out-list">
            <b-card>
                <h1>Clock Out Selection</h1>
                <p>You are currently clocked-in for more than one client. Please choose a client you'd like to clock-out for.</p>
                <div class="">
                    @foreach($shifts as $shift)
                        <div class="btn-client">
                            <b-btn href="/clock-out/{{$shift->id}}" size="lg" variant="info" class="btn-block" :disabled="authInactive">{{$shift->client->name}}</b-btn>
                        </div>
                    @endforeach
                </div>
            </b-card>
        </b-col>
    </b-row>
@endsection
