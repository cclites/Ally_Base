@extends('layouts.app')

@section('title', 'Caregiver - No Bank Account - Pending Deposit')

@section('content')
    <business-caregiver-deposits-missing-bank-account :businesses="{{ $businesses }}"></business-caregiver-deposits-missing-bank-account>
@endsection