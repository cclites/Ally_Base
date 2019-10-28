@extends('layouts.app')

@section('title', 'Note Imports')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item active">Note Imports</li>
@endsection

@section('content')
    <admin-note-import></admin-note-import>
@endsection
