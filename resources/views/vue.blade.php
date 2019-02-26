@extends('layouts.'.$layout)

@section('title', $title)

@if($breadcrumbs !== null)
    @section('breadcrumbs')
        @if(!count($breadcrumbs))
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
        @else
            @foreach($breadcrumbs as $name => $url)
                <li class="breadcrumb-item"><a href="{{ $url }}">{{ $name }}</a></li>
            @endforeach
        @endif
        <li class="breadcrumb-item active">{{ $title }}</li>
    @endsection
@endif

@section('content')
    <{!! $component !!}
    @foreach($props as $prop => $value)
        :{!! $prop !!}='@json($value)'
    @endforeach
    ></{!! $component !!}>
@endsection