@extends('layouts.print')

@section('content')
    @include('layouts.partials.print_logo')
    <style>
        .mt {
            margin-top: 1.2rem;
        }

        .img-signature > img {
            max-width: 400px;
            width: 400px;
        }
    </style>
    <div class="container">
        <!-- Client Personal Data -->
        <div class="panel panel-default">
            <div class="panel-heading">Client Personal Data</div>
            <div class="panel-body">
                <div class="row mt">
                    <div class="col doc-item">First Name</div>
                    <div class="col doc-item">{{ $onboarding->client->firstname }}</div>
                </div>

                <div class="row mt">
                    <div class="col doc-item">Middle Initial</div>
                    <div class="col doc-item">{{ $onboarding->middle_initial }}</div>
                </div>

                <div class="row mt">
                    <div class="col doc-item">Last Name</div>
                    <div class="col doc-item">{{ $onboarding->client->lastname }}</div>
                </div>

                <div class="row mt">
                    <div class="col doc-item">Email</div>
                    <div class="col doc-item">{{ $onboarding->client->email }}</div>
                </div>

                <div class="row mt">
                    <div class="col doc-item">Phone Number</div>
                    <div class="col doc-item">{{ $onboarding->phone_number }}</div>
                </div>

                <div class="row mt">
                    <div class="col doc-item">Date of Birth</div>
                    <div class="col doc-item">{{ $onboarding->client->date_of_birth }}</div>
                </div>

                <div class="row mt">
                    <div class="col doc-item">Gender</div>
                    <div class="col doc-item">
                        @if ($onboarding->client->gender == 'M')
                            <span>Male</span>
                        @elseif ($onboarding->client->gender == 'F')
                            <span>Female</span>
                        @endif
                    </div>
                </div>

                <div class="row mt">
                    <div class="col doc-item">Address</div>
                    <div class="col doc-item">{{ $onboarding->address }}</div>
                </div>

                <div class="row mt">
                    <div class="col doc-item">Does the client live in a facility or subdivision?</div>
                    <div class="col doc-item">
                        @if ($onboarding->facility)
                            <span>Yes</span>
                            @if ($onboarding->facility_instructions)
                                <div>
                                    {{ $onboarding->facility_instructions }}
                                </div>
                            @endif
                        @else
                            <span>No</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- General Information -->
        <div class="panel panel-default">
            <div class="panel-heading">General Information</div>
            <div class="panel-body">
                <div class="row mt">
                    <div class="col doc-item">Primary Medical Condition(s)</div>
                    <div class="col doc-item">{{ $onboarding->primary_conditions }}</div>
                </div>
                <div class="row mt">
                    <div class="col doc-item">Reason(s) for Service</div>
                    <div class="col doc-item">{{ $onboarding->service_reasons }}</div>
                </div>
                <div class="row mt">
                    <div class="col doc-item">Goal(s) of Service</div>
                    <div class="col doc-item">{{ $onboarding->service_goals }}</div>
                </div>
                <div class="row mt">
                    <div class="col doc-item">Allergies (Food/Medication)</div>
                    <div class="col doc-item">{{ $onboarding->allergies }}</div>
                </div>
                <div class="row mt">
                    <div class="col doc-item">Medical Equipment in the Home (ex: wheelchair, walker, oxygen)</div>
                    <div class="col doc-item">{{ $onboarding->medical_equipment }}</div>
                </div>
                <div class="row mt">
                    <div class="col doc-item">Client Height</div>
                    <div class="col doc-item">{{ $onboarding->height }}</div>
                </div>
                <div class="row mt">
                    <div class="col doc-item">Client Weight</div>
                    <div class="col doc-item">{{ $onboarding->weight }}</div>
                </div>
            </div>
        </div>

        <!-- Hands-On Care Activities -->
        <div class="panel panel-default">
            <div class="panel-heading">Hands-On Care Activities Requested</div>
            <div class="panel-body">
                @foreach($onboarding->activities->where('category', 'hands_on') as $activity)
                    <div class="row">
                        <div class="col doc-item">{{ $activity->name }}</div>
                        <div class="col doc-item">{{ ucfirst($activity->pivot->assistance_level) }}</div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Household Care Activities -->
        <div class="panel panel-default">
            <div class="panel-heading">Household Activities Requested</div>
            <div class="panel-body">
                @foreach($onboarding->activities->where('category', 'household') as $activity)
                    <div class="row">
                        <div class="col doc-item">{{ $activity->name }}</div>
                        <div class="col doc-item">{{ ucfirst($activity->pivot->assistance_level) }}</div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Primary Care Physician & Pharmacy -->
        <div class="row">
            <div class="col">
                <div class="panel panel-default">
                    <div class="panel-heading">Primary Care Physician</div>
                    <div class="panel-body">
                        <div class="row mt">
                            <div class="col doc-item">Name</div>
                            <div class="col doc-item">{{ $onboarding->physician_name }}</div>
                        </div>
                        <div class="row mt">
                            <div class="col doc-item">Phone</div>
                            <div class="col doc-item">{{ $onboarding->physician_phone }}</div>
                        </div>
                        <div class="row mt">
                            <div class="col doc-item">Address</div>
                            <div class="col doc-item">{{ $onboarding->physician_address }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="panel panel-default">
                    <div class="panel-heading">Pharmacy</div>
                    <div class="panel-body">
                        <div class="row mt">
                            <div class="col doc-item">Name</div>
                            <div class="col doc-item">{{ $onboarding->pharmacy_name }}</div>
                        </div>
                        <div class="row mt">
                            <div class="col doc-item">Phone</div>
                            <div class="col doc-item">{{ $onboarding->pharmacy_phone }}</div>
                        </div>
                        <div class="row mt">
                            <div class="col doc-item">Address</div>
                            <div class="col doc-item">{{ $onboarding->pharmacy_address }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Prescription Medication List  --}}
        <div class="panel panel-default">
            <div class="panel-heading">Prescription Medication List</div>
            <div class="panel-body">
                <table class="table">
                    <tr>
                        <th>Type</th>
                        <th>Dose</th>
                        <th>Frequency</th>
                    </tr>
                    @foreach($onboarding->client->medications as $medication)
                        <tr>
                            <td>{{ $medication->type }}</td>
                            <td>{{ $medication->dose }}</td>
                            <td>{{ $medication->frequency }}</td>
                        </tr>
                    @endforeach
                </table>

            </div>
        </div>

        <!-- Hospice Information -->
        <div class="panel panel-default">
            <div class="panel-heading">Hospice Information</div>
            <div class="panel-body">
                <div class="row mt">
                    <div class="col doc-item">Is the client under Hospice Care?</div>
                    <div class="col doc-item">
                        @if ($onboarding->hospice_care)
                            <span>Yes</span>
                        @else
                            <span>No</span>
                        @endif
                    </div>
                </div>
                @if ($onboarding->hospice_care)
                    <div class="row mt">
                        <div class="col doc-item">Office Location (city)</div>
                        <div class="col doc-item">{{ $onboarding->hospice_office_location }}</div>
                    </div>
                    <div class="row mt">
                        <div class="col doc-item">Client Service Coordinator Name</div>
                        <div class="col doc-item">{{ $onboarding->hospice_case_manager }}</div>
                    </div>
                    <div class="row mt">
                        <div class="col doc-item">Phone</div>
                        <div class="col doc-item">{{ $onboarding->hospice_phone }}</div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Do Not Resuscitate -->
        <div class="panel panel-default">
            <div class="panel-heading">Do Not Resuscitate (DNR)</div>
            <div class="panel-body">
                <div class="row mt">
                    <div class="col doc-item">Does the client have a DNR?</div>
                    <div class="col doc-item">
                        @if ($onboarding->dnr)
                            <span>Yes</span>
                        @else
                            <span>No</span>
                        @endif
                    </div>
                </div>
                @if ($onboarding->dnr)
                    <div class="row mt">
                        <div class="col doc-item">Where is the DNR posted?</div>
                        <div class="col doc-item">{{ $onboarding->dnr_location }}</div>
                    </div>
                @endif
            </div>
        </div>

        <div class="row">
            <div class="col">
                <div class="panel panel-default">
                    <div class="panel-heading">Primary Emergency Contact</div>
                    <div class="panel-body">
                        <div class="row mt">
                            <div class="col doc-item">Name</div>
                            <div class="col doc-item">{{ $onboarding->ec_name }}</div>
                        </div>
                        <div class="row mt">
                            <div class="col doc-item">Address</div>
                            <div class="col doc-item">{{ $onboarding->ec_address }}</div>
                        </div>
                        <div class="row mt">
                            <div class="col doc-item">Phone</div>
                            <div class="col doc-item">{{ $onboarding->ec_phone_number }}</div>
                        </div>
                        <div class="row mt">
                            <div class="col doc-item">Email</div>
                            <div class="col doc-item">{{ $onboarding->ec_email }}</div>
                        </div>
                        <div class="row mt">
                            <div class="col doc-item">Relationship</div>
                            <div class="col doc-item">{{ $onboarding->ec_relationship }}</div>
                        </div>
                        <div class="row mt">
                            <div class="col doc-item">POA</div>
                            <div class="col doc-item">
                                @if($onboarding->ec_poa)
                                    <span>Yes</span>
                                @else
                                    <span>No</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="panel panel-default">
                    <div class="panel-heading">Secondary Emergency Contact</div>
                    <div class="panel-body">
                        <div class="row mt">
                            <div class="col doc-item">Name</div>
                            <div class="col doc-item">{{ $onboarding->secondary_ec_name }}</div>
                        </div>
                        <div class="row mt">
                            <div class="col doc-item">Address</div>
                            <div class="col doc-item">{{ $onboarding->secondary_ec_address }}</div>
                        </div>
                        <div class="row mt">
                            <div class="col doc-item">Phone</div>
                            <div class="col doc-item">{{ $onboarding->secondary_ec_phone_number }}</div>
                        </div>
                        <div class="row mt">
                            <div class="col doc-item">Email</div>
                            <div class="col doc-item">{{ $onboarding->secondary_ec_email }}</div>
                        </div>
                        <div class="row mt">
                            <div class="col doc-item">Relationship</div>
                            <div class="col doc-item">{{ $onboarding->secondary_ec_relationship }}</div>
                        </div>
                        <div class="row mt">
                            <div class="col doc-item">POA</div>
                            <div class="col doc-item">
                                @if($onboarding->secondary_ec_poa)
                                    <span>Yes</span>
                                @else
                                    <span>No</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Emergency Management Plan -->
        <div class="panel panel-default">
            <div class="panel-heading">Emergency Management Plan</div>
            <div class="panel-body">
                <p>In the event of a weather emergency or natural disaster...</p>
                <div class="row mt">
                    <div class="col doc-item">Will the client leave the region?</div>
                    <div class="col doc-item">
                        @if($onboarding->emp_leave_region)
                            <span>Yes</span>
                        @else
                            <span>No</span>
                        @endif
                    </div>
                </div>
                <div class="row mt">
                    <div class="col doc-item">If leaving, with whom and where?</div>
                    <div class="col doc-item">
                        {{ $onboarding->emp_with_who_where }}
                    </div>
                </div>
                <div class="row mt">
                    <div class="col doc-item">Will the client remain in his/her home?</div>
                    <div class="col doc-item">
                        @if ($onboarding->emp_remain_home)
                            <span>Yes</span>
                        @else
                            <span>No</span>
                        @endif
                    </div>
                </div>
                <div class="row mt">
                    <div class="col doc-item">Is the client going to a shelter?</div>
                    <div class="col doc-item">
                        @if ($onboarding->emp_shelter)
                            <span>Yes</span>
                            <div class="row">
                                <div class="col doc-item">What type of shelter?</div>
                                <div class="col doc-item">{{ $onboarding->emp_shelter_type }}</div>
                            </div>
                            <div class="row mt">
                                <div class="col doc-item">Where is the shelter?</div>
                                <div class="col doc-item">{{ $onboarding->emp_shelter_address }}</div>
                            </div>
                        @else
                            <span>No</span>
                        @endif
                    </div>
                </div>
                <div class="row mt">
                    <div class="col doc-item">Would you like help with shelter registration?</div>
                    <div class="col doc-item">
                        @if ($onboarding->emp_shelter_registration_assistance)
                            <span>Yes</span>
                        @else
                            <span>No</span>
                        @endif
                    </div>
                </div>
                <div class="row mt">
                    <div class="col doc-item">Who will be responsible for the client during an evacuation?</div>
                    <div class="col doc-item">{{ $onboarding->emp_evacuation_responsible_party }}</div>
                </div>
                <div class="row mt">
                    <div class="col doc-item">Will the client need a caregiver during this period?</div>
                    <div class="col doc-item">
                        @if ($onboarding->emp_caregiver_required)
                            <span>Yes</span>
                        @else
                            <span>No</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Preferences & Other Information -->
        <div class="panel panel-default">
            <div class="panel-heading">Preferences & Other Information</div>
            <div class="panel-body">
                <div class="row mt">
                    <div class="col doc-item">Caregiver Gender Preference</div>
                    <div class="col doc-item">
                        @if ($onboarding->cg_gender_pref == 'M')
                            <span>Male</span>
                        @endif
                        @if ($onboarding->cg_gender_pref == 'F')
                            <span>Female</span>
                        @endif
                    </div>
                </div>
                <div class="row mt">
                    <div class="col doc-item">Caregiver Attire Preference</div>
                    <div class="col doc-item">
                        @if ($onboarding->cg_attire_pref == 'scrubs')
                            <span>Scrubs</span>
                        @endif
                        @if ($onboarding->cg_attire_pref == 'business_casual')
                            <span>Business Casual</span>
                        @endif
                    </div>
                </div>
                <div class="row mt">
                    <div class="col doc-item">Does the client have pets?</div>
                    <div class="col doc-item">
                        @if ($onboarding->pets)
                            <span>Yes</span>
                        @else
                            <span>No</span>
                        @endif
                    </div>
                </div>
                @if ($onboarding->pets)
                    <div class="row mt">
                        <div class="col doc-item">What kind of pets and how many?</div>
                        <div class="col doc-item">{{ $onboarding->pets_description }}</div>
                    </div>
                    <div class="row mt">
                        <div class="col doc-item">Will the caregiver assist with pet care?</div>
                        <div class="col doc-item">
                            @if ($onboarding->cg_pet_assistance)
                                <span>Yes</span>
                            @else
                                <span>No</span>
                            @endif
                        </div>
                    </div>
                @endif
                <div class="row mt">
                    <div class="col doc-item">Will the client need assistance with transportation?</div>
                    <div class="col doc-item">
                        @if ($onboarding->transportation)
                            <span>Yes</span>
                        @else
                            <span>No</span>
                        @endif
                    </div>
                </div>
                @if ($onboarding->transportation)
                    <div class="row mt">
                        <div class="col doc-item">Who's vehicle is preferred?</div>
                        <div class="col doc-item">
                            {{ $onboarding->transportation_vehicle }}
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Requested Start Date & Schedule -->
        <div class="panel panel-default">
            <div class="panel-heading">Client Personal Data</div>
            <div class="panel-body">
                <div class="row mt">
                    <div class="col doc-item">Requested Start Date</div>
                    <div class="col doc-item">{{ $onboarding->requested_start_at }}</div>
                </div>
                <div class="row mt">
                    <div class="col doc-item">Requested Schedule</div>
                    <div class="col doc-item">{{ $onboarding->requested_schedule }}</div>
                </div>
            </div>
        </div>

        <!-- Client Signature -->
        <div class="panel panel-default">
            <div class="panel-heading">Client Signature</div>
            <div class="panel-body">
                <div class="row mt">
                    <div class="col">
                        <div class="img-signature">
                            @php
                                $content = str_replace('width="800"', 'width="300"', $onboarding->signature->content);
                                $content = str_replace('height="300"', 'height="112"', $content);
                            @endphp
                            {!! $content !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
