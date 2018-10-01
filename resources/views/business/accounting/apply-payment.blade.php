@extends('layouts.app')

@section('title', 'Apply Payment')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item">Accounting</li>
    <li class="breadcrumb-item active">Apply Payment</li>
@endsection

@section('content')
    @if(app()->environment() !== 'demo')
        <div class="alert alert-warning">
            <strong>Coming soon: </strong> This feature is not yet active on your account.
        </div>
    @endif

    <business-apply-payment :clients="{{ $clients }}" />
@endsection
