@extends('layouts.app')

@section('title', 'Pending Charges')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item active">Pending Charges</li>
@endsection

@section('content')
    <admin-pending-charges></admin-pending-charges>
@endsection