@extends('layouts.guest')

@section('title', 'Confirmation')

@section('content')
    <client-confirmation id="{{ $encrypted_id }}" :client="{{ $client }}" :user="{{ $client->user }}" phone-number="{{ $phoneNumber }}" :address="{{ $client->evvAddress OR '{}' }}"></client-confirmation>
@endsection