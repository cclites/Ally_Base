@extends('layouts.guest')

@section('title', 'Client Account Setup')

@section('content')
    <client-setup-wizard :client-data="{{ $client }}" token="{{ $token }}"></client-setup-wizard>
@endsection