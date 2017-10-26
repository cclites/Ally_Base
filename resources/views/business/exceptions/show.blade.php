@extends('layouts.app')

@section('title', $exception->title)

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('business.exceptions.index') }}">Exceptions</a></li>
    <li class="breadcrumb-item active">{{ $exception->title }}</li>
@endsection

@section('content')
    <business-exception :exception="{{ $exception }}" :acknowledger="{{ $exception->acknowledger OR 'null' }}"></business-exception>
@endsection