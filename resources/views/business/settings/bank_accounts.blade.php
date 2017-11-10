@extends('layouts.app')

@section('title', 'Business Settings')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item active">Business Settings</li>
@endsection

@section('content')
    <!-- Nav tabs -->
    <ul class="nav nav-pills with-padding-bottom" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" data-toggle="tab" href="#bank_accounts" role="tab">Bank Accounts</a>
        </li>
        {{--<li class="nav-item">--}}
            {{--<a class="nav-link" data-toggle="tab" href="#other_stuff" role="tab">Other Stuff</a>--}}
        {{--</li>--}}
    </ul>

    <div class="tab-content">
        <div class="tab-pane active" id="bank_accounts" role="tabpanel">
            <div class="row">
                <div class="col-lg-6">
                    <b-card header="Deposit Bank Account"
                            border-variant="primary"
                            header-bg-variant="info"
                            header-text-variant="white">

                        <bank-account-form :account="{{ $business->bankAccount ? json_encode($business->bankAccount) : json_encode(new stdClass()) }}" :submit-url="'/business/settings/bank-account/deposit'" />
                    </b-card>
                </div>
                <div class="col-lg-6">
                    <b-card header="Payment Bank Account"
                            border-variant="primary"
                            header-bg-variant="info"
                            header-text-variant="white">
                        <bank-account-form :account="{{ $business->paymentAccount ? json_encode($business->paymentAccount) : json_encode(new stdClass()) }}" :submit-url="'/business/settings/bank-account/payment'" />
                    </b-card>
                </div>
            </div>
        </div>

        {{--<div class="tab-pane" id="other_stuff" role="tabpanel">--}}
            {{--other--}}
        {{--</div>--}}
    </div>
@endsection