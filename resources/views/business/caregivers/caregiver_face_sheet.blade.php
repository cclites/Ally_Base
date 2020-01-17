@extends('layouts.print')

@section('content')
    <style>
        table{
            width: 100%;
        }
    </style>

    <table class="logo_header">
        <tr>
            <td>
                {{-- Business Logo --}}
                {{ $caregiver->business->logo }}
            </td>
            <td>{{-- Business Address --}}
                {{ $caregiver->business->name }}<br>
                {{ $caregiver->business->address1 }}<br>
                @if($caregiver->business->address2)
                    {{$caregiver->business->address2}}<br>
                @endif
                {{ $caregiver->business->getCityStateZipAttribute() }}
            </td>
        </tr>
    </table>

    {{-- Name --}}
    {{-- Birthdate --}}
    {{-- Years Experience (do not have) --}}
    {{-- Works with Pets --}}
    {{-- Works with Smokers --}}
    {{-- Minimum hours (availability) --}}
    {{-- Maximum distance (availability) --}}
    {{-- Credentials --}}
    {{-- Services (skills) --}}
    {{-- Transfer Type (add to client table) --}}
    {{-- Biography (do not have) --}}

@endsection