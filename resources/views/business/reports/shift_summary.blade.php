@extends('layouts.app')

@section('title', 'Shifts by ' . ucfirst($type))

@section('content')
    <shift-summary-report type="{{ $type }}" :users="{{ $users }}" />
@endsection
