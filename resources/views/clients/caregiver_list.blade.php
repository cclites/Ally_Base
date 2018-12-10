@extends('layouts.app')

@section('title', 'My Caregivers')

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <client-caregiver-list :caregivers="{{ $caregivers }}"/>
        </div>
    </div>
@endsection
