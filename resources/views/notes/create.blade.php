@extends('layouts.app')


@section('title', 'Add Note')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item active">Add Note</li>
@endsection

@section('content')
    <note-create :business="{{ json_encode($business) }}"></note-create>
@endsection