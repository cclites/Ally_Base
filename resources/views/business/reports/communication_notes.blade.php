@extends('layouts.print')

@section('title', "Notes")

@push('head')
    <style>
        .logo img {
            max-height: 80px;
        }

        table tr td{
            padding: 0 6px;
            width: 200px;
        }

        table tfoot tr td{
            padding-top: 40px;
        }
    </style>
@endpush

@section('content')

    <div class="page" id="summary">
        <div class="h4">Notes</div>
        <div>
            <table>
                <thead>
                    <tr>
                        <th>Note Date</th>
                        <th>Type</th>
                        <th>Caregiver</th>
                        <th>Client</th>
                        <th>Prospect</th>
                        <th>Referral Source</th>
                        <th>Preview</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($data as $item)
                    <tr>
                        <td>{{ $item['created_at'] }}</td>
                        <td>{{ $item['type'] }}</td>
                        <td>{{ $item['caregiver'] }}</td>
                        <td>{{ $item['client'] }}</td>
                        <td>{{ $item['prospect'] }}</td>
                        <td>{{ $item['referral_source'] }}</td>
                        <td>{{ $item['body'] }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

@endsection
