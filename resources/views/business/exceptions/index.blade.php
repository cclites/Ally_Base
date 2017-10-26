@extends('layouts.app')

@section('title', 'Exceptions')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item active">Exceptions</li>
@endsection

@section('content')
    <!-- Nav tabs -->
    <ul class="nav nav-pills with-padding-bottom" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" data-toggle="tab" href="#active-exceptions" role="tab">Active Exceptions</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#archived-exceptions" role="tab">Acknowledged Exceptions</a>
        </li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane active" id="active-exceptions" role="tabpanel">
            <div class="row">
                <div class="col-lg-12">
                    <business-exception-list :exceptions="{{ $exceptions }}" title="Active Exceptions"></business-exception-list>
                </div>
            </div>
        </div>
        <div class="tab-pane" id="archived-exceptions" role="tabpanel">
            <div class="row">
                <div class="col-lg-12">
                    <business-exception-list :exceptions="{{ $archived }}" title="Acknowledged Exceptions"></business-exception-list>
                </div>
            </div>
        </div>
    </div>
@endsection