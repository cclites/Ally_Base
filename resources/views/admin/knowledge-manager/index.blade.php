@extends('layouts.app')

@section('title', 'Knowledge Base Manager')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item active">Knowledge Base Manager</li>
@endsection

@section('content')
    <div class="row col-12 with-padding-bottom">
        <a href="/knowledge-base" target="_blank" class="btn btn-lg btn-info">View the Knowledge Base</a>
    </div>
    <knowledge-manager :knowledge-base="{{ $knowledge }}" />
@endsection