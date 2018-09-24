@extends('layouts.app')

@section('title', 'Printable Schedules')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/business/reports">Reports</a></li>
    <li class="breadcrumb-item active">Printable Schedules</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header text-white bg-info">
            <h4 class="text-white">Printable Schedules</h4>
        </div>
        <div class="card-body">
            <form action="/business/schedule/print" method="post" target="_blank">
                {{ csrf_field() }}
                <div class="row">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label for="">Start Date:</label>
                            <input type="text"
                                   class="form-control datepicker"
                                   name="start_date"
                                   required
                                   value="{{ old('start_date', \Carbon\Carbon::now()->format('m/d/Y')) }}">
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <label for="">End Date:</label>
                        <input type="text"
                               class="form-control datepicker"
                               required
                               name="end_date"
                               value="{{ old('end_date', \Carbon\Carbon::now()->addDays(3)->format('m/d/Y')) }}">
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <button class="btn btn-info" type="submit">Generate</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $('.datepicker').datepicker();
    </script>
@endpush