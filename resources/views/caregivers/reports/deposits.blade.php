@extends('layouts.app')

@section('title', 'Deposit History')

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
            <h2>Coming Soon</h2>
            <p>Metrics above do not reflect real deposit amounts.</p>
        </div>
    </div>
@endsection