@extends('layouts.guest')

@section('title', 'Please confirm your information below.')

@section('breadcrumbs', 'Then, click the "Accept and Verify" button at the bottom of the page.')

@section('content')
    <client-confirmation id="{{ $encrypted_id }}" :client="{{ $client }}" :user="{{ $client->user }}" phone-number="{{ $phoneNumber }}" :address="{{ $client->evvAddress OR '{}' }}"></client-confirmation>
@endsection