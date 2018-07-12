@extends('layouts.app')

@section('title', 'Caregiver Applications')

@section('content')
    <caregiver-application-list :business="{{ json_encode(activeBusiness()) }}"
                                :applications="{{ json_encode($applications) }}">
    </caregiver-application-list>
@endsection