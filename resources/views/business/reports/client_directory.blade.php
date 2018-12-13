 @extends('layouts.app')

@section('title', 'Clients Directory')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/business/reports">Reports</a></li>
    <li class="breadcrumb-item active">Client Directory</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <client-directory :clients="{{ $clients }}"></client-directory>
        </div>
    </div>
@endsection