@extends('layouts.guest')

@section('title', 'Ally is now processing payments for ' . $business->name ?? '')

@section('breadcrumbs', 'Please confirm your information below, then, click the "Accept and Verify" button at the bottom of the page.')

@section('content')
    <caregiver-confirmation token="{{ $token }}"
                         :caregiver="{{ $caregiver }}"
                         phone-number="{{ $phoneNumber }}"
                         :address="{{ $address OR '{}' }}"
                         terms-url="{{ $termsUrl }}"
    >
    </caregiver-confirmation>
@endsection