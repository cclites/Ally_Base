@extends('layouts.app')

@section('title', 'Caregiver - No Bank Account')

@section('content')
    <business-caregiver-deposits-missing-bank-account :caregivers="{{ $caregivers }}"></business-caregiver-deposits-missing-bank-account>
@endsection