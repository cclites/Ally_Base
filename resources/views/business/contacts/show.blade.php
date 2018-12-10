@extends('layouts.app')

@section('title', isset($contact) ? 'Edit Contact' : 'New Contact')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('business.contacts.index') }}">Other Contacts</a></li>
    <li class="breadcrumb-item active">{{ $contact->name ?? 'New Contact' }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <contact-edit :contact="{{ $contact ?? 'null' }}"></contact-edit>
        </div>
    </div>
@endsection
