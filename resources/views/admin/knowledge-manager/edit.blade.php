@extends('layouts.app')

@section('title', 'Knowledge Base Manager')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/admin/knowledge-manager">Knowledge Base Manager</a></li>
    <li class="breadcrumb-item active">{{ empty($knowledge) ? 'Create' : 'Edit' }}</li>
@endsection

@section('content')
    <knowledge-editor :knowledge="{{ empty($knowledge) ? '{}' : $knowledge }}" />
@endsection
