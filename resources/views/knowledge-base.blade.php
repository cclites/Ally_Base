@extends('layouts.app')

@section('title', 'Knowledge Base')

@section('content')
    <knowledge-base :knowledge-base="{{ $knowledge }}" />
@endsection
