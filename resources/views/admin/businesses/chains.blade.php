@extends('layouts.app')

@section('title', 'Chain List')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item active">Business Chains</li>
@endsection

@section('content')
    <business-chain-list :chains="{{ $chains ?? '[]' }}"></business-chain-list>
@endsection