@extends('layouts.app')

@section('title', 'Referral Sources')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item active">Referral Sources</li>
@endsection

@section('content')
    <client-referral :referral-sources="{{ $referralsources }}" :edit-source-id="{{ $edit ?? 0 }}" :create-source="{{ $create ?? 0 }}"></client-referral>
@endsection
