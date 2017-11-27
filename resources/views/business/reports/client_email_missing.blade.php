@extends('layouts.app')

@section('title', 'Clients Missing Email')

@section('content')
    <clients-without-emails-report :clients="{{ $clients }}"></clients-without-emails-report>
@endsection