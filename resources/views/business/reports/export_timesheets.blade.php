@extends('layouts.app')

@section('title', 'Export Timesheets')

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <business-export-timesheets :clients="{{ $clients }}" :caregivers="{{ $caregivers }}" token="{{ csrf_token() }}"></business-export-timesheets>
        </div>
    </div>
@endsection