@extends('layouts.app')

@section('title', 'Shift History')

@section('content')
    <shift-history :shifts="{{ $shifts }}"></shift-history>
@endsection