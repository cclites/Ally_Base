@extends('layouts.app')

@section('title', 'Franchises')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
@endsection

@section('content')
    <business-franchisor-dashboard></business-franchisor-dashboard>
@endsection
