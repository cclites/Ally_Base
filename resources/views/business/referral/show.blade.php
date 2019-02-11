@extends('layouts.app')

@section('title', 'Referral Source')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item">Referral Sources</li>
    <li class="breadcrumb-item"><a href="{{ route('business.referral-sources.index') }}">{{ ucfirst($referralSource->type) }}</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
    <ul class="nav nav-pills with-padding-bottom hidden-lg-down profile-tabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" data-toggle="tab" href="#profile" role="tab">Profile</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#client_notes" role="tab">Notes</a>
        </li>
    </ul>

    <!-- Smaller device tabs -->
    <ul class="nav nav-pills with-padding-bottom hidden-xl-up profile-tabs" role="tablist">
        <li class="nav-item dropdown">
            <a class="nav-link active dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">Change Tab: <span class="tab-name">Profile</span></a>
            <div class="dropdown-menu">
                <a class="dropdown-item" data-toggle="tab" href="#profile" role="tab">Profile</a>
                <a class="dropdown-item" data-toggle="tab" href="#client_notes" role="tab">Notes</a>
            </div>
        </li>
    </ul>

    <!-- Tab panes -->
    <div class="tab-content">
        <div class="tab-pane active" id="profile" role="tabpanel">
            <business-referral-source :source="{{ $referralSource ?? 'null' }}"></business-referral-source>
        </div>
        <div class="tab-pane" id="client_notes" role="tabpanel">
            <notes-tab :notes="{{ $referralSource->notes }}" :source="{{ $referralSource }}"></notes-tab>
        </div>
    </div>
@endsection
