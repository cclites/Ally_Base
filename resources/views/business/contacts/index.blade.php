@extends('layouts.app')

@section('title', 'Contact List')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item active">Other Contacts</li>
@endsection

@section('content')
    @if($active_business)
        <contact-list>
        </contact-list>
    @endif
@endsection
