@extends('layouts.app')

@section('title', 'Shift History')

@section('content')
    <shift-history :clients="{{ $clients }}"></shift-history>
@endsection