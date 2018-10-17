@extends('layouts.app')

@section('title', 'Client Referal')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item active">Client Referal Souces</li>
@endsection

@section('content')
    <client-referal :referral-sources="{{ $referralsources }}"></client-referal>
@endsection
