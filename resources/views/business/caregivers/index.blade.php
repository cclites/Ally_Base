@extends('layouts.app')

@section('title', 'Caregiver List')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item active">Caregivers</li>
@endsection

@section('content')
    @if(is_admin_now())
        <admin-business-select :business="{{ $active_business OR '{}' }}"></admin-business-select>
    @endif
    @if($active_business)
        <caregiver-list
            :caregivers="{{ $caregivers }}"
            :multi_location="{{ json_encode($multiLocation) }}">
        </caregiver-list>
    @endif
@endsection
