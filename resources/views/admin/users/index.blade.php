@extends('layouts.app')

@section('title', 'User List')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item active">Users</li>
@endsection

@section('content')
    <admin-user-list></admin-user-list>
@endsection