@extends('layouts.app')

@section('title', 'Location List')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item active">Business Locations</li>
@endsection

@section('content')
    <business-list :businesses="{{ $businesses ?? '[]' }}"></business-list>
@endsection