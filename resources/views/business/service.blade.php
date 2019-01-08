@extends('layouts.app')

@section('title', 'Service')

@section('content')
    <business-service :services="{{ $services }}"></business-service>
@endsection
