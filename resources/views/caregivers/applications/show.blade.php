@extends('layouts.app')

@section('title', 'Caregiver Application')

@section('content')
    <caregiver-application :application="{{ json_encode($application) }}">
    </caregiver-application>
@endsection