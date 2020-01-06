@extends('layouts.print')

@section('content')

    @include('layouts.partials.print_logo')

    <style>
        table{
            width: 100%;
        }
    </style>

    <h2>Client Medication Details for {{ $client->nameLastFirst }}</h2>

    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Dose</th>
                <th>Frequency</th>
                <th>Description</th>
                <th>Route</th>
                <th>New/Changed</th>
                <th>Tracking</th>
                <th>Side Effects</th>
                <th>Notes</th>
            </tr>
        </thead>
        <tbody>
            @foreach($client->medications as $medication)
            <tr>
                <td>{{ $medication->type }}</td>
                <td>{{ $medication->dose }}</td>
                <td>{{ $medication->frequency }}</td>
                <td>{{ $medication->description }}</td>
                <td>{{ $medication->route }}</td>
                <td>{{ $medication->wasChanged ? "Yes" : "No" }}</td>
                <td>{{ $medication->tracking }}</td>
                <td>{{ $medication->side_effects }}</td>
                <td>{{ $medication->notes }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

@endsection

