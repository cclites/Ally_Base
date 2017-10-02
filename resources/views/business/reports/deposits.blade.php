@extends('layouts.app')

@section('title', 'Deposit History')

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <business-deposit-history :deposits="{{ $deposits }}"></business-deposit-history>
        </div>
    </div>
@endsection