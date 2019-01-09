@extends('layouts.app')

@section('title', 'Payers')

@section('content')
    <business-payer-list :payers="{{ $payers }}"></business-payer-list>
@endsection
