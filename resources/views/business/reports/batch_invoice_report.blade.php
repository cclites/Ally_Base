@extends('layouts.app')

@section('title', 'Batch Invoice Report')

@section('breadcrumbs')
   <li class="breadcrumb-item"><a href="/">Home</a></li>
   <li class="breadcrumb-item"><a href="/business/reports">Reports</a></li>
   <li class="breadcrumb-item active">Batch Invoice Report</li>
@endsection

@section('content')
   <batch-invoice-report :clients="{{ $clients }}"></batch-invoice-report>
@endsection