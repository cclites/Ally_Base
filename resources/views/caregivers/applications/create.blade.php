@extends('layouts.blank')

@section('title', 'Caregiver Application')

@section('content')
    <caregiver-application-create :business="{{ json_encode($business) }}">
    </caregiver-application-create>
@endsection