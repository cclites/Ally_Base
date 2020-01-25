@extends('layouts.guest')

@section('title', 'Please confirm your information below.')

@section('breadcrumbs', 'Then, click the "Accept and Verify" button at the bottom of the page.')

@section('content')
    <client-confirmation token="{{ $token }}"
                         :client="{{ $client }}"
                         phone-number="{{ $phoneNumber }}"
                         :address="{{ $client->evvAddress ?? '{}' }}"
                         terms-url="{{ $termsUrl }}"
                         terms="{{ $terms }}"
    >
    </client-confirmation>
@endsection