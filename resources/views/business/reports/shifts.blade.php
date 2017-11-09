@extends('layouts.app')

@push('head')
    <link href="https://fonts.googleapis.com/css?family=Homemade+Apple" rel="stylesheet">
    <style>
        .signature {
            font-family: 'Homemade Apple', cursive;
            font-size: 1.1rem;
        }
    </style>
@endpush

@section('title', 'Shift History')

@section('content')
    <business-shift-history :shifts="{{ $shifts }}"></business-shift-history>
@endsection