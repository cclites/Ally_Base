@extends('layouts.app')

@section('title', 'Pending Deposits')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item active">Pending Deposits</li>
@endsection

@section('content')
    <admin-deposits :chains="{{ $chains }}"></admin-deposits>
@endsection