@extends('layouts.app')

@section('title', 'Shift History')

@section('content')
    <business-shift-history :shifts="{{ $shifts }}"></business-shift-history>
@endsection