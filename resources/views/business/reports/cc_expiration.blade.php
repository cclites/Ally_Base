@extends('layouts.app')

@section('title', 'Credit Card Expiration')

@section('content')
    <cc-expiration-report :cards="{{ $cards }}"></cc-expiration-report>
@endsection