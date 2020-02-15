@extends('layouts.print')

@push('head')
    <style>
        table{
            width: 100%;
            display: table;
            table-layout: fixed;
        }

        div.day{
            padding: 2px;
            font-size: 10px;
            display: block;
            text-align: right;
        }

        div.event{
            font-size: 8px;
            margin: 3px;
            padding: 2px;
            border: 1px solid #aaaaaa;
            border-radius: 2px;
        }

        table th{
            text-align: center;
            color: #fff;
            background-color: #0b67cd;
        }

        table td{
            vertical-align: top;
        }

        table,
        table tr,
        table th,
        table td{
            border: 1px solid #bbb;
        }

        table tr,
        table th,
        table td{
            min-height: 100px;
        }

        h2{
            text-align: center;
        }
    </style>
@endpush


@section('content')
    @php echo($html); @endphp
@endsection
