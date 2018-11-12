@extends('layouts.app')

@section('title', 'Knowledge Base')

@section('content')
    <knowledge-base :knowledge-base="{{ $knowledge }}"
        :admin="{{ auth()->user()->role_type == 'admin' ? 'true' : 'false' }}"
    />
@endsection
