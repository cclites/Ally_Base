@extends('layouts.app')

@section('title', $exception->title)

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item">Exceptions</li>
    <li class="breadcrumb-item active">{{ $exception->title }}</li>
@endsection

@section('content')

@endsection