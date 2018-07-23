@extends('layouts.app')

@section('title', 'Claims Report')

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <ltci-claims-report token="{{ csrf_token() }}"></ltci-claims-report>
        </div>
    </div>
@endsection