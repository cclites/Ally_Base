@extends('layouts.app')

@section('title', 'Franchises')
@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item active">Franchises</li>
@endsection

@section('content')
    <business-franchisees>

    </business-franchisees>
@endsection
