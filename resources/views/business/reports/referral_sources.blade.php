@extends('layouts.app')

@section('title', 'Referral Sources')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/business/reports">Reports</a></li>
    <li class="breadcrumb-item active">Referral Sources</li>
@endsection

@section('content')
    <referral-sources-report  :reports="{{ $reports }}"/>
@endsection