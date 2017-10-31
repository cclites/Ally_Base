@extends('layouts.blank')

@section('title', 'Caregiver Application')

@section('content')
    <caregiver-application-create :business="{{ json_encode($business) }}"
                                  :positions="{{ json_encode($positions) }}">
    </caregiver-application-create>
@endsection