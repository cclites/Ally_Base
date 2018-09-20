@extends('layouts.app')

@section('title', 'Care Match')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item active">Care Match</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <business-care-match></business-care-match>
        </div>
    </div>
@endsection
