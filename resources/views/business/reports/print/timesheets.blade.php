@extends('layouts.print')

@section('title', 'Export Timesheets')

@section('content')
    @include('business.reports.print.timesheets_contents')
@endsection