@extends('layouts.print')

@section('content')

<style>
    .logo img {
        max-height: 80px;
    }
</style>

@include('layouts.partials.print_logo')

<div class="page" id="summary">
    <div class="h4">Notes for {{ $user['firstname'] . " " . $user['lastname'] }}</div>
    <hr>
    <table>
        <thead></thead>
        <tbody>
            @foreach($user->notes as $note)
                <tr>
                    <td colspan="2"><strong>{{ $note->title }}</strong></td>
                </tr>
                <tr>
                    <td>Created By: {{ $note->creator->name }}</td>
                    <td class="align-right">{{ \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $note->created_at)->format('m/d/Y  h:i:s A') }}</td>
                </tr>
                <tr>
                    <td style="padding-bottom: 15px;">{{ $note->body }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

@endSection