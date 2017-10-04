@extends('layouts.app')

@section('title') Documents @endsection

@section('content')
<form method="post" action="/documents" enctype="multipart/form-data">
    {{ csrf_field() }}
    <div class="form-group">
        <input type="file" name="document">
    </div>
    <input type="submit" value="Upload" class="btn btn-success">
</form>
@endsection
