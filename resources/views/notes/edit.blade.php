@extends('layouts.app')


@section('title', 'Add Note')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item active">Add Note</li>
@endsection

@section('content')
    <note-edit :note="{{ json_encode($note) }}"></note-edit>
@endsection