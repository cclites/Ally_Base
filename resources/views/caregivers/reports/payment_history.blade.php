@extends('layouts.app')

@section('title', 'Payment History')

@section('content')
    <caregiver-payment-history :caregiver="{{ $caregiver }}" :deposits="{{ $deposits }}"></caregiver-payment-history>
@endsection