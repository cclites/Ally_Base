@extends('layouts.app')

@section('title', 'Caregiver - No Bank Account')

@section('content')
    <business-caregiver-deposits-missing-bank-account :businesses="{{ json_encode($results) }}"></business-caregiver-deposits-missing-bank-account>
@endsection