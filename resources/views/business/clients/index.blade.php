@extends('layouts.app')

@section('title', 'Client List')

@section('content')
    <client-list :clients="{{ $clients }}"></client-list>
@endsection