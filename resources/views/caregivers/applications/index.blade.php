@extends('layouts.app')

@section('title', 'Caregiver Applications')

@section('content')
    <caregiver-application-list :business="{{ json_encode($business) }}"
                                :applications="{{ json_encode($applications) }}"
                                :positions="{{ json_encode($positions) }}"
                                :statuses="{{ json_encode($statuses) }}">
    </caregiver-application-list>
@endsection