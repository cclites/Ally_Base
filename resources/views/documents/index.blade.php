@extends('layouts.app')

@section('title') Documents @endsection

@section('content')
<form method="post" action="/documents" enctype="multipart/form-data" class="form-inline">
    {{ csrf_field() }}
    <div class="form-group">
        <input type="file" name="document">
    </div>
    <input type="submit" value="Upload" class="btn btn-success">
</form>
<hr>
@if ($documents->count())
    <table class="table">
        <thead><tr><th>File</th></tr></thead>
        <tbody>
        @foreach ($documents as $document)
            <tr><td>{{ $document->original_filename }}</td></tr>
        @endforeach
        </tbody>
    </table>
@endif
@endsection
