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
    @include('layouts.partials.print_logo')

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
                        <td>{{ $item->created_at }}</td>
                        <td>{{ $item->type }}</td>
                        <td>{{ $item->caregiver ? $item->caregiver->nameLastFirst() : '' }}</td>
                        <td>{{ $item->client ? $item->client->name : '' }}</td>
                        <td>{{ $item->prospect ? $item->prospect->name() : '' }}</td>
                        <td>{{ $item->referral_source ? $item->referral_source->organization : '' }}</td>
                        {{-- TODO: The body will probably need a formatter --}}
                        <td>{{ $item->body }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

@endsection
