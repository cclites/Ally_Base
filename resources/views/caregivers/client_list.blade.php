@extends('layouts.app')

@section('title', 'My Clients')

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <caregiver-client-list :clients="{{ $clients }}"/>
        </div>
    </div>
@endsection
