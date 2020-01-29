@extends('layouts.print')

@section('title', 'Projected Billing')

@section('content')
    @include('layouts.partials.print_logo')
    <table class="w-100">
        <tr>
            <td class="w-50">
                <div class="h4">Total hours scheduled: {{ number_format(data_get($stats, 'total_hours'), 2) }}</div>
            </td>
            <td class="w-50">
                <div class="h4">Total clients scheduled: {{ data_get($stats, 'total_clients') }}</div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="h4">Projected Billing: ${{ number_format(data_get($stats, 'projected_total'), 2) }}</div>
            </td>
            <td>
            </td>
        </tr>
        <tr>
            <td>
                <table class="mt-3 w-100">
                    @foreach ($clientTypeStats as $type)
                        <tr>
                            <td class="w-50">
                                <i class="fa fa-chevron-right mr-1"></i>{{ Str::title(data_get($type, 'name')) }}:
                            </td>
                            <td class="w-50">${{ number_format(data_get($type, 'projected_billing'), 2) }}</td>
                        </tr>
                    @endforeach
                </table>
            </td>
        </tr>
    </table>
    <hr>
    <div class="row">
        <div class="col">
            <table class="table">
                <thead>
                <tr>
                    <th>Client Name</th>
                    <th>Hours</th>
                    <th>Projected Billing</th>
                </tr>
                </thead>
                <tbody>
                @foreach($clientStats as $stat)
                    <tr>
                        <td>{{ data_get($stat, 'name') }}</td>
                        <td>{{ number_format(data_get($stat, 'hours'), 2) }}</td>
                        <td>${{ number_format(data_get($stat, 'projected_billing'), 2) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
