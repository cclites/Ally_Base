@extends('layouts.print')

@section('content')

    @include('layouts.partials.print_logo')

    <style>
        table{
            width: 100%;
        }
    </style>

    <h2>Client Goal Details for {{ $client->nameLastFirst }}</h2>

    <table>
        <thead>
            <tr>
                <th>Questions</th>
                <th>Tracked on clock-out</th>
            </tr>
        </thead>
        <tbody>
            @foreach($client->goals as $goal)
            <tr>
                <td>{{ $goal->question }}</td>
                <td>{{ $goal->track_goal_progress ? "Yes" : "No" }}</td>
            </tr>
                @endforeach
        </tbody>
    </table>

@endsection