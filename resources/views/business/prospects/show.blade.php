@extends('layouts.app')

@section('title', isset($prospect) ? 'Edit Prospect' : 'New Prospect')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('business.prospects.index') }}">Prospects</a></li>
    <li class="breadcrumb-item active">{{ $prospect->name ?? 'New Prospect' }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <prospect-edit :prospect="{{ $prospect ?? 'null' }}"></prospect-edit>
        </div>
    </div>
@endsection
