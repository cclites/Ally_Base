@extends('layouts.print')

@section('title', 'Export Timesheets')

@section('content')
    @include('layouts.partials.print_logo')

    @include('business.reports.print.timesheets_contents')
@endsection