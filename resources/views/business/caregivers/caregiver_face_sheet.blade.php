@extends('layouts.print')

@section('content')
    <style>
        table{
            width: 100%;
            margin-bottom: 20px;
        }

        th{
            background-color: #1e88e5;
            color: #fff;
        }

        .caregiver-info, .caregiver-info th, .caregiver-info td,
        .credential-info, .credential-info th, .credential-info td,
        .profile-info, .profile-info th, .profile-info td,
        .credential-info, .credential-info th, .credential-info td,
        .services-info, .services-info th, .services-info td{
            border: 1px solid #4d575d;
            padding: 6px;
            border-spacing: 2px;
        }

        p{
            margin-bottom: 0px;
            font-size: 10px;
        }

        .logo_header tr td{
            width: 50%;
            text-align: center;
        }

        .avatar{
            text-align: center;
            width: 50%;
            height: auto;
        }

        .logo_header tr td,
        .avatar img{
            height: 100px;
        }

        .heading span{
            width: 48%;
            display: inline-block;
        }
    </style>

    <table class="logo_header">
        <tbody>
            <tr>
                <td>
                    <img src="{{ $business->logo }}" alt="{{ $business->name }}">
                </td>
                <td>
                    {{ $business->name }}<br>
                    {{ $business->address1 }}<br>
                    @if($business->address2)
                        {{ $business->address2}}<br>
                    @endif
                    {{ $business->getCityStateZipAttribute() }}
                </td>
            </tr>
        </tbody>
    </table>

    <hr>

    <div class="heading">
        <span>Caregiver Data for {{ $caregiver->nameLastFirst() }}</span>
        <span class="avatar"><img src="{{ $caregiver->avatar }}" alt="{{ $caregiver->avatar }}"></span>
    </div>

    <hr>

    <table class="caregiver-info">
        <thead>
            <tr>
                <th colspan="3">CAREGIVER INFORMATION:</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <p>Name:</p>
                    {{ $caregiver->first_name . " " . $caregiver->last_name }}
                </td>
                <td>
                    <p>DOB:</p>
                    {{ \Carbon\Carbon::parse( $caregiver->date_of_birth )->format( 'm-d-Y' ) }}
                </td>
                <td>
                    <p>City:</p>
                    @if($caregiver->addresses->count())
                    {{  $caregiver->addresses->first()->city }}
                    @endif

                </td>
            </tr>
        </tbody>
    </table>

    {{-- Years Experience (do not have) --}}
    <table class="profile-info">
        <thead>
            <tr>
                <th colspan="3">PROFILE INFORMATION:</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    @if($caregiver->pets_dogs_okay || $caregiver->pets_cats_okay || $caregiver->pets_birds_okay)
                        <input type="checkbox" checked="true">
                    @else
                        <input type="checkbox">
                    @endif
                    Works with Pets
                </td>
                <td>
                    @if($caregiver->smoking_okay )
                        <input type="checkbox" checked="true">
                    @else
                        <input type="checkbox">
                    @endif
                    Works with Smokers
                </td>
            </tr>
            <tr>
                <td>Minimum Hours per Shift: {{ $caregiver->availability->minimum_shift_hours }}</td>
                <td>Maximum Distance Willing to Travel: {{ $caregiver->availability->maximum_miles }}</td>
            </tr>
        </tbody>
    </table>

    <table class="credential-info">
        <thead>
            <tr>
                <th>
                    Credentials:
                </th>
            </tr>
        </thead>
        <tbody>
            <tr>

                <td>
                    @if($caregiver->title)
                        {{ $caregiver->title }}
                    @else
                        No Credentials supplied
                    @endif
                </td>
            </tr>
        </tbody>
    </table>

    <table class="services-info">
        <thead>
        <tr>
            <th>
                Services:
            </th>
        </tr>
        </thead>
        <tbody>
            @if( count($caregiver->skills) )
                @foreach($caregiver->skills as $skill)
                    <tr>
                        <td>{{ $skill->name }}</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td>
                        No skills selected
                    </td>
                </tr>

            @endif
        </tbody>
    </table>
    {{-- Transfer Type (do not have) --}}
    {{-- Biography (do not have) --}}

@endsection