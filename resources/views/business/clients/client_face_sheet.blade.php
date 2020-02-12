@extends('layouts.print')

@section('content')
    <style>
        table{
            width: 100%;
            margin-bottom: 20px;
        }

        .address-info th{
            width: 50%;
        }

        th{
            background-color: #1e88e5;
            color: #fff;
        }
        .client-info, .client-info th, .client-info td,
        .contact-info, .contact-info th, .contact-info td,
        .profile-info, .profile-info th, .profile-info td,
        .address-info, .address-info th, .address-info td,
        .services-info, .services-info th, .services-info td,
        .emergency-info, .emergency-info th, .emergency-info td{
            border: 1px solid #4d575d;
            padding: 6px;
            border-spacing: 2px;
        }

        p{
            margin-bottom: 0px;
            font-size: 10px;
        }

        .avatar,
        .logo_header tr td{
            text-align: center;
        }

        .logo_header img,
        .avatar img{
            height: auto;
            width: 20%;
            border: none;
        }

        .heading span{
            width: 48%;
            display: inline-block;
        }

    </style>

    <table class="logo_header">
        <tr>
            <td>
                <img src="{{ $client->business->logo }}" alt="{{ $client->business->name }}">
            </td>
            <td>
                {{ $client->business->name }}<br>
                {{ $client->business->address1 }}<br>
                @if($client->business->address2)
                    {{$client->business->address2}}<br>
                @endif
                {{ $client->business->getCityStateZipAttribute() }}
            </td>
        </tr>
    </table>
    <hr>

    <div class="heading">
        <span>Client Data for {{ $client->nameLastFirst() }}</span>
        <span class="avatar"><img src="{{ $client->avatar }}"></span>
    </div>
    <hr>

    <table class="client-info">
        <thead>
            <tr>
                <th colspan="3">CLIENT INFORMATION:</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <p>First Name:</p>
                    {{ $client->first_name }}
                </td>
                <td>
                    <p>Last Name:</p>
                    {{ $client->last_name }}
                </td>
                <td>
                    <p>DOB:</p>
                    {{ \Carbon\Carbon::parse( $client->date_of_birth )->format( 'm-d-Y' ) }}
                </td>
            </tr>
        </tbody>
    </table>

    {{-- Client Relationship (do not have) --}}

    <table class="contact-info">
        <thead>
            <tr>
                <th colspan="2">CONTACT INFORMATION:</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <p>Phone:</p>
                    @if($client->getPhoneNumber())
                        {{ $client->getPhoneNumber()->first()->number() }}
                    @endif
                </td>
                <td>
                    <p>Email:</p>
                    @if($client->email)
                        {{ $client->email }}
                    @endif
                </td>
            </tr>
        </tbody>
    </table>

    <table class="address-info">
        <thead>
            <tr>
                <th colspan="2" class="col-6">MAILING ADDRESS:</th>
                <th colspan="2">PHYSICAL ADDRESS:</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="2">
                    <p>Address 1:</p>
                    @if($client->billingAddress)
                        {{ $client->billingAddress->address1 }}
                    @endif
                </td>
                <td colspan="2">
                    <p>Address 1</p>
                    @if($client->evvAddress)
                        {{ $client->evvAddress->address1 }}
                    @endif
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <p>Address 2</p>
                    @if($client->billingAddress)
                        {{ $client->billingAddress->address2 }}
                    @endif
                </td>
                <td colspan="2">
                    <p>Address 2</p>
                    @if($client->evvAddress)
                        {{ $client->evvAddress->address2 }}
                    @endif
                </td>
            </tr>
            <tr>
                <td>
                    <p>City:</p>
                    @if($client->billingAddress)
                        {{ $client->billingAddress->city }}
                    @endif
                </td>
                <td>
                    <p>State:</p>
                    @if($client->billingAddress)
                        {{ $client->billingAddress->state }}
                    @endif
                </td>
                <td>
                    <p>City:</p>
                    @if($client->evvAddress)
                        {{ $client->evvAddress->city }}
                    @endif
                </td>
                <td>
                    <p>State:</p>
                    @if($client->evvAddress)
                        {{ $client->evvAddress->state }}
                    @endif
                </td>
            </tr>
            <tr>
                <td>
                    <p>County:</p>
                    @if($client->billingAddress)
                        {{ $client->billingAddress->county }}
                    @endif
                </td>
                <td>
                    <p>Zip:</p>
                    @if($client->billingAddress)
                        {{ $client->billingAddress->zip }}
                    @endif
                </td>

                <td>
                    <p>County:</p>
                    @if($client->evvAddress)
                        {{ $client->evvAddress->county }}
                    @endif
                </td>
                <td>
                    <p>Zip:</p>
                    @if($client->evvAddress)
                        {{ $client->evvAddress->zip }}
                    @endif
                </td>
            </tr>
        </tbody>
    </table>

    {{-- Power of attorney (do not have) --}}

    <table class="profile-info">
        <thead>
            <tr>
                <th colspan="2">PROFILE:</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    @if($client->careDetails && $client->careDetails->smoker == 1)
                        <input type="checkbox" checked="checked">
                    @else
                        <input type="checkbox">
                    @endif
                    Smoker
                </td>
                <td>

                    @if($client->careDetails && count($client->careDetails->pets))
                        <input type="checkbox" checked="checked">
                    @else
                        <input type="checkbox">
                    @endif

                    Pets in Home &nbsp;&nbsp; Type:
                    @if($client->careDetails && count($client->careDetails->pets))
                        @foreach($client->careDetails->pets as $pet)
                        {{ $pet . "  "}}
                        @endforeach
                     @endif

                </td>
            </tr>
        </tbody>
    </table>

    {{-- Requested Certification (do not have) --}}

    <table class="emergency-info">
        <thead>
            <tr>
                <th colspan="3">EMERGENCY CONTACT INFORMATION:</th>
            </tr>
        </thead>
        <tbody>
            @if($client->contacts->count())
                @foreach($client->contacts as $contact)
                    <tr>
                        <td><p>Name: </p>{{ $contact->getFirstNameAttribute() . " " . $contact->getLastNameAttribute() }}</td>
                        <td colspan="2"><p>Relationship:</p>{{ $contact->relationship }}</td>
                    </tr>
                    <tr>
                        <td>
                            <p>Mobile Phone:</p> {{ $contact->phone1 }}
                        </td>
                        <td>
                            <p>Home Phone:</p> {{ $contact->phone2 }}
                        </td>
                        <td>
                            <p>Work Phone:</p> {{ $contact->work_phone }}
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3"><p>Address:</p> {{ $contact->address }}</td>
                    </tr>
                    <tr>
                        <td><p>City:</p> {{ $contact->city }}</td>
                        <td><p>State:</p>{{ $contact->state }}</td>
                        <td><p>Zip:</p>{{ $contact->zip }}</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td>Client has no emergency contact info.</td>
                </tr>
            @endif
        </tbody>
    </table>

    <table class="services-info">
        <thead>
        <tr>
            <th>
                Requested Services:
            </th>
        </tr>
        </thead>
        <tbody>
        @if( count($activities) )
            @foreach($activities as $activity)
                <tr>
                    <td>{{ $activity }}</td>
                </tr>
            @endforeach
        @else
            <tr>
                <td>
                    No Services Requested
                </td>
            </tr>
        @endif
        </tbody>
    </table>

    {{-- Requested Days (do not have) --}}
    {{-- Current Needs (do not have) --}}
    {{-- Bio (do not have --}}
    {{-- Caregiver Preferences (do not have)--}}


@endsection