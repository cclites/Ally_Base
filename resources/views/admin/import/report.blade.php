@extends('layouts.app')

@section('title', 'Import History')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item active"><a href="{{ route('admin.imports.index') }}">Import History</a></li>
@endsection

@section('content')
    <admin-import-report></admin-import-report>
@endsection
