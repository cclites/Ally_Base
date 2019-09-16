@extends('layouts.app')

@section('title', ucwords($type) . ' Referral Sources')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/business/reports">Reports</a></li>
    <li class="breadcrumb-item active">{{ ucwords($type) }} Referral Sources</li>
@endsection

@section('content')
    <referral-sources-report source-type="{{ $type }}" />
@endsection
