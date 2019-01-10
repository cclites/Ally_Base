@extends('layouts.app')

@section('title', 'Notes')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item active">Notes</li>
@endsection

@section('content')
    <note-list></note-list>
@endsection