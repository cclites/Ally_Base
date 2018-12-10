@extends('layouts.blank')

@section('title', 'Caregiver Application')

@section('content')
    <caregiver-application-create :business-chain="{{ json_encode($businessChain) }}">
    </caregiver-application-create>
@endsection