@extends('layouts.app')

@section('title', 'Payment History')

@section('content')
    <b-card title="Payment History">
        <caregiver-deposit-history :caregiver="{{ $caregiver }}" :deposits="{{ $deposits }}"></caregiver-deposit-history>
    </b-card>
@endsection