@extends('layouts.app')

@section('title', 'Caregiver Deposits without Bank Account')

@section('content')
    <business-caregiver-deposits-missing-bank-account :businesses="{{ $businesses }}"></business-caregiver-deposits-missing-bank-account>
@endsection