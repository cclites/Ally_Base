@extends('layouts.guest')

@section('title', 'Please confirm your information below.')

@section('breadcrumbs', 'Then, click the "Accept and Verify" button at the bottom of the page.')

@section('content')
    <caregiver-confirmation token="{{ $token }}"
                         :caregiver="{{ $caregiver }}"
                         phone-number="{{ $phoneNumber }}"
                         :address="{{ $address OR '{}' }}"
                         terms-url="{{ $termsUrl }}"
    >
    </caregiver-confirmation>
@endsection