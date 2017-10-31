@extends('layouts.app')

@section('title', 'Certification Expirations')

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <business-certification-expirations :certifications="{{ $certifications OR '[]' }}"></business-certification-expirations>
        </div>
    </div>
@endsection