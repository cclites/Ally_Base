<?php
/**
 * @var string $renderedInvoiceHtml
 */
?>
@extends('layouts.print')

@section('title', 'Invoices')

@push('head')
    @include('invoices.partials.styles')
@endpush

@section('content')
    {!! $renderedInvoiceHtml !!}
@endsection
