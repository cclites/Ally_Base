@extends('layouts.app')

@section('title', 'Client List')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item active">Clients</li>
@endsection

@section('content')
    @if(is_admin_now())
        <admin-business-select :business="{{ $active_business ?? '{}' }}"></admin-business-select>
    @endif
    @if($active_business)
        <client-list></client-list>
    @endif
@endsection
