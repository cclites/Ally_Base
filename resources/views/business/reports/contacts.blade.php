@extends('layouts.app')

@section('title', 'Contacts Report')

@section('content')
    <contacts-report type="{{ $type }}" />
@endsection
