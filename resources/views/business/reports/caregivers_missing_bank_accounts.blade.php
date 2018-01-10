@extends('layouts.app')

@section('title', 'Caregivers Missing Bank Accounts')

@section('content')

    <caregivers-missing-bank-accounts :caregivers="{{ $caregivers }}"></caregivers-missing-bank-accounts>
@endsection