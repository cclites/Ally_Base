@extends('layouts.app')

@section('title', 'Payment History')

@section('content')
    <caregiver-payment-history :caregiver="{{ $caregiver }}" :payments="{{ $payments }}"></caregiver-payment-history>
@endsection