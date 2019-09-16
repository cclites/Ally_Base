 @extends('layouts.app')

@section('title', 'Caregivers Directory')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/business/reports">Reports</a></li>
    <li class="breadcrumb-item active">Caregiver Directory</li>
@endsection

@section('content')

    <div class="row">

        <div class="col-lg-12">

            <caregiver-directory :custom-fields="{{ $fields }}"></caregiver-directory>
        </div>
    </div>
@endsection