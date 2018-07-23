@extends('layouts.app')

@section('title', 'Caregiver Application Edit')

@section('content')
    <caregiver-application-edit :application="{{ json_encode($application) }}"
                                :business="{{ json_encode($business) }}"></caregiver-application-edit>
@endsection