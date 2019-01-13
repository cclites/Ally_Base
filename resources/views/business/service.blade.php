@extends('layouts.app')

@section('title', 'Service Codes')

@section('content')
    <business-service :services="{{ $services }}"></business-service>
@endsection
