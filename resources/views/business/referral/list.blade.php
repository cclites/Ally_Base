@extends('layouts.app')

@section('title', ucfirst($type) . ' Referral Sources')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item">Referral Sources</li>
    <li class="breadcrumb-item active">{{ ucfirst($type) }}</li>
@endsection

@section('content')
    <business-referral-source-manager
        source-type="{{ $type }}"
        :referral-sources="{{ $referralsources }}" 
        :edit-source-id="{{ $edit ?? 0 }}"
        :create-source="{{ $create ?? 0 }}"
    ></business-referral-source-manager>
@endsection
