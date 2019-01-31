@extends('layouts.app')

@section('title', 'Client Statistics')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/business/reports">Reports</a></li>
    <li class="breadcrumb-item active">Client Statistics</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <client-stats :client-types="{{ $clientTypes }}"></client-stats>
        </div>
    </div>
@endsection
