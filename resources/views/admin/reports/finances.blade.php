@extends('layouts.app')

@section('title', 'Financial Summary')


@section('content')
    <admin-financial-summary :providers="{{ json_encode($businesses) }}"></admin-financial-summary>
@endsection