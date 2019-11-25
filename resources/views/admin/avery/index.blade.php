@extends('layouts.app')

@section('title', 'Avery 5160 Printout')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item active">Avery 5160 Printout</li>
@endsection

@section('content')
    <avery-printout :chains="{{ $chains OR '[]' }}"></avery-printout>
@endsection