@extends('layouts.app')

@section('title', 'Payers')

@section('content')
    <business-payer-list :payers="{{ $payers }}" :services="{{ $services }}"></business-payer-list>
@endsection
