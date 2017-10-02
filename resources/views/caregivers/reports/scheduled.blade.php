@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-lg-4">
            <dashboard-metric variant="info" value="$0" text="This Month" />
        </div>
        <div class="col-lg-4">
            <dashboard-metric variant="primary" value="$0" text="Scheduled" />
        </div>
        <div class="col-lg-4">
            <dashboard-metric variant="success" value="$0" text="Year to Date" />
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">

        </div>
    </div>
@endsection