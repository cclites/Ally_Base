 @extends('layouts.app')

@section('title', 'Prospects Directory')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/business/reports">Reports</a></li>
    <li class="breadcrumb-item active">Prospect Directory</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <prospect-directory :prospects="{{ $prospects }}"></prospect-directory>
        </div>
    </div>
@endsection