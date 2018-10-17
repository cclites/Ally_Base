@extends('layouts.app')

@section('title', 'Add New Referal Source')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item active">Add New Referal Source</li>
@endsection

@section('content')
    <div class="col-md-3">
        <add-client-referal></add-client-referal>
    </div>
@endsection
