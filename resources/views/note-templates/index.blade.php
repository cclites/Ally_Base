@extends('layouts.app')

@section('title', 'Note Templates')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item active">Note Templates</li>
@endsection

@section('content')
    Note Template here
    <note-template-list :templates="{{ $templates }}"></note-template-list>
@endsection