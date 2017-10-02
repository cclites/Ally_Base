@extends('layouts.app')

@section('title', 'Add a New Client')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('business.clients.index') }}">Clients</a></li>
    <li class="breadcrumb-item active">Add Client</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <client-create></client-create>
        </div>
    </div>
@endsection
