@extends('layouts.print')

@push('head')
    <style>
        .row{
            margin-left: 0px;
        }

        div.row span{
            margin-left: 2px;
            margin-right: 8px;
        }

        div.row input[type='checkbox']{
            /*margin-left: 8px;
            margin-right: 2px;*/
        }

        input[type=checkbox] {
            -moz-appearance:none;
            -webkit-appearance:none;
            -o-appearance:none;
            outline: none;
            content: none;
        }

        input[type=checkbox]:before {
            font-family: "FontAwesome";
            content: "\f00c";
            font-size: 15px;
            color: transparent !important;
            background: #ffffff;
            display: block;
            border: 1px solid black;
            border-radius: 2px;
        }

        input[type=checkbox]:checked:before {

            color: black !important;
        }

    </style>
@endpush

@section('content')

    <h2>Client Care Details for {{ $client->nameLastFirst }}</h2>

    <div class="row">
        <b-col>
            <strong>{{ $client->nameLastFirst() }}</strong><br>
            @if($client->getBillingAddress())
                <strong>Address:</strong><br>
                {{ $client->getBillingAddress()->getStreetAddressAttribute() }}<br>
                {{ $client->getBillingAddress()->getCityStateZipAttribute() }}<br>
            @endif
            @if($client->getPhoneNumber())
            <strong>Phone: </strong>{{ $client->getPhoneNumber()->number }}
            @endif

        </b-col>
    </div>

    <div class="row mt-4 mb-2"><strong>Functional Limitations</strong></div>

    <div class="row functional-limitations">
        @php  $checked = in_array('amputation', $client->careDetails['functional']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Amputation</span>

        @php  $checked = in_array('incontinence', $client->careDetails['functional']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Incontinence</span>

        @php  $checked = in_array('contracture', $client->careDetails['functional']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Contracture</span>

        @php  $checked = in_array('hearing', $client->careDetails['functional']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Hearing</span>

        @php  $checked = in_array('paralysis', $client->careDetails['functional']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Paralysis</span>

        @php  $checked = in_array('endurance', $client->careDetails['functional']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Endurance</span>

        @php  $checked = in_array('ambulation', $client->careDetails['functional']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Ambulation</span>

        @php  $checked = in_array('blind', $client->careDetails['functional']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Legally Blind</span>

        @php  $checked = in_array('dyspnea', $client->careDetails['functional']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Dyspnea with Minimal Exertion</span>

        <br>

        @php  $checked = in_array('other', $client->careDetails['functional']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Other</span>
    </div>

    <div class="row mt-4 mb-2">
        Other: {{ $client->careDetails['functional_other'] }}
    </div>

    <div class="row mt-4 mb-2"><strong>Client Mobility</strong></div>

    <div class="row mobility">
        @php  $checked = in_array('bedrest', $client->careDetails['mobility']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Complete Bedrest</span>

        @php  $checked = in_array('bedrest_brp', $client->careDetails['mobility']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Bedrest BRP</span>

        @php  $checked = in_array('hoyer_lift', $client->careDetails['mobility']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Hoyer Lift</span>

        @php  $checked = in_array('independent', $client->careDetails['mobility']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Independent At Home</span>

        @php  $checked = in_array('wheelchair', $client->careDetails['mobility']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Wheelchair</span>

        @php  $checked = in_array('no_restrictions', $client->careDetails['mobility']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>No Restrictions</span>

        @php  $checked = in_array('turn', $client->careDetails['mobility']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Turn/reposition every 2 hours</span>

        @php  $checked = in_array('assist_transfers', $client->careDetails['mobility']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Assist with Transfers</span>

        @php  $checked = in_array('assist_ambulation', $client->careDetails['mobility']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Assist with Ambulation</span>

        @php  $checked = in_array('cane', $client->careDetails['mobility']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Cane</span>

        @php  $checked = in_array('up_as_tolerated', $client->careDetails['mobility']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Up as Tolerated</span>

        @php  $checked = in_array('partial_weight', $client->careDetails['mobility']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Partial Weight Bearing</span>

        @php  $checked = in_array('walker', $client->careDetails['mobility']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Walker</span>

        @php  $checked = in_array('hospital_bed', $client->careDetails['mobility']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Hospital Bed</span>

        @php  $checked = in_array('crutches', $client->careDetails['mobility']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Crutches</span>

        @php  $checked = in_array('exercises_prescribed', $client->careDetails['mobility']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Exercises Prescribed</span>

        <br>

        @php  $checked = in_array('other', $client->careDetails['mobility']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Other</span>
    </div>

    <div class="row mt-4 mb-2">
        Other: {{ $client->careDetails['mobility_other'] }}
    </div>

    <div class="row mt-4 mb-2"><strong>Specialty Instructions</strong></div>

    <div class="row mobility_instructions">
        <p>{{ $client->careDetails['mobility_instructions'] }}</p>
    </div>

    <div class="row mt-4 mb-2"><strong>Mental Status</strong></div>

    <div class="row mental mb-2">
        @php  $checked = in_array('oriented', $client->careDetails['mental_status']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Oriented</span>

        @php  $checked = in_array('comatose', $client->careDetails['mental_status']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Comatose</span>

        @php  $checked = in_array('forgetful', $client->careDetails['mental_status']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Forgetful</span>

        @php  $checked = in_array('depressed', $client->careDetails['mental_status']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Depressed</span>

        @php  $checked = in_array('disoriented', $client->careDetails['mental_status']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Disoriented</span>

        @php  $checked = in_array('lethargic', $client->careDetails['mental_status']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Lethargic</span>

        @php  $checked = in_array('other', $client->careDetails['mental_status']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Other</span>
    </div>

    <div class="row mt-4 mb-2"><strong>Prognosis</strong></div>

    <div class="row prognosis">
        @php  $checked = 'poor' == $client->careDetails['prognosis'] ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Poor</span>

        @php  $checked = 'guarded' == $client->careDetails['prognosis'] ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Guarded</span>

        @php  $checked = 'fair' == $client->careDetails['prognosis'] ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Fair</span>

        @php  $checked = 'good' == $client->careDetails['prognosis'] ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Good</span>

        @php  $checked = 'excellent' == $client->careDetails['prognosis'] ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Excellent</span>
    </div>

    <hr/>

    <h3>General</h3>

    <div class="row">
        Height: <span>{{ $client->careDetails['height'] }}</span><br>
        Weight: <span>{{ $client->careDetails['weight'] }}</span><br>
    </div>

    <div class="row mt-4 mb-2"><strong>Level of Competency</strong></div>

    <div class="row competency">
        @php  $checked = 'alert' == $client->careDetails['competency_level'] ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Alert</span>

        @php  $checked = 'forgetful' == $client->careDetails['competency_level'] ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Forgetful</span>

        @php  $checked = 'confused' == $client->careDetails['competency_level'] ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Confused</span>

        @php  $checked = 'other' == $client->careDetails['competency_level'] ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Other</span>
    </div>

    <div class="row mt-4 mb-2"><strong>Living Arrangements</strong></div>

    <div class="row arrangements">
        @php  $checked = $client->careDetails['lives_alone'] ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Lives Alone</span>

        @php  $checked = !$client->careDetails['lives_alone'] ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Others living in same location</span>
    </div>

    <div class="row mt-4 mb-2"><strong>Pets</strong></div>

    <div class="row pets">
        @php  $checked = in_array('cats', $client->careDetails['pets']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Cats</span>

        @php  $checked = in_array('dogs', $client->careDetails['pets']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Dogs</span>

        @php  $checked = in_array('birds', $client->careDetails['pets']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Birds</span>
    </div>

    <div class="row mt-4 mb-2"><strong>Smoker</strong></div>

    <div class="row smoker">
        @php  $checked = $client->careDetails['smoker'] ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Yes</span>

        @php  $checked = !$client->careDetails['smoker'] ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>No</span>
    </div>

    <div class="row mt-4 mb-2"><strong>Alcohol</strong></div>

    <div class="row alcohol">
        @php  $checked = $client->careDetails['alcohol'] ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Yes</span>

        @php  $checked = !$client->careDetails['alcohol'] ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>No</span>
    </div>

    <div class="row mt-4 mb-2"><strong>Has consumer ever been deemed incompetent by licensed professional</strong></div>

    <div class="row incompetent">
        @php  $checked = $client->careDetails['incompetent'] ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Yes</span>

        @php  $checked = !$client->careDetails['incompetent'] ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>No</span>
    </div>

    <hr/>

    <h3>Medication</h3>

    <div class="row mt-4 mb-2"><strong>Is consumer able to provide direction to the caregiver to assist consumer in taking medication?</strong></div>

    <div class="row medication_direction">
        @php  $checked = $client->careDetails['assist_medications'] ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Yes</span>

        @php  $checked = !$client->careDetails['assist_medications'] ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>No</span>
    </div>

    <div class="row mt-4 mb-2"><strong>Self-Administered Medications</strong></div>

    <div class="row can_provide_direction">
        @php  $checked = $client->careDetails['can_provide_direction'] ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Remind/Prompt</span>

        @php  $checked = !$client->careDetails['can_provide_direction'] ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Assist</span>
    </div>

    <div class="row mt-4 mb-2"><strong>Who is responsible for overseeing medications and where they are located?</strong></div>

    <div class="row medication_overseer">
        <p>{{ $client->careDetails['medication_overseer'] }}</p>
    </div>

    <div class="row mt-4 mb-2"><strong>Allergies</strong></div>

    <div class="row allergies">
        <p>{{ $client->careDetails['allergies'] }}</p>
    </div>

    <div class="row mt-4 mb-2"><strong>Pharmacy Name</strong></div>

    <div class="row pharmacy_name">
        <p>{{ $client->careDetails['pharmacy_name'] }}</p>
    </div>

    <div class="row mt-4 mb-2"><strong>Pharmacy Number</strong></div>

    <div class="row pharmacy_number">
        <p>{{ $client->careDetails['pharmacy_number'] }}</p>
    </div>

    <hr/>

    <h3>Care Details</h3>

    <div class="row mt-4 mb-2"><strong>Safety Measures</strong></div>

    <div class="row safety_measures">
        @php  $checked = in_array('can_leave_alone', $client->careDetails['safety_measures']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Client may be left alone</span>

        @php  $checked = in_array('contact_guard', $client->careDetails['safety_measures']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Contact Guard</span>

        @php  $checked = in_array('gait_belt', $client->careDetails['safety_measures']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Gait Belt</span>

        @php  $checked = in_array('can_use_stairs', $client->careDetails['safety_measures']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Client may use stairs</span>

        @php  $checked = in_array('stair_lift', $client->careDetails['safety_measures']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Stair Lift</span>

        @php  $checked = in_array('other', $client->careDetails['safety_measures']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Other</span>
    </div>

    <div class="row mt-4 mb-2"><strong>Toileting</strong></div>


    <div class="row toileting">
        @php  $checked = in_array('continent', $client->careDetails['toileting']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Continent</span>

        @php  $checked = in_array('catheter', $client->careDetails['toileting']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Catheter</span>

        @php  $checked = in_array('bedpan', $client->careDetails['toileting']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Bedpan</span>

        @php  $checked = in_array('incontinent', $client->careDetails['toileting']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Incontinent</span>

        @php  $checked = in_array('colostomy', $client->careDetails['toileting']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Colostomy</span>

        @php  $checked = in_array('urinal', $client->careDetails['toileting']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Urinal</span>

        @php  $checked = in_array('adult_briefs', $client->careDetails['toileting']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Adult Briefs</span>

        @php  $checked = in_array('bathroom', $client->careDetails['toileting']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Bathroom</span>

        @php  $checked = in_array('bedside_commode', $client->careDetails['toileting']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Bedside Commode</span>
    </div>

    <div class="row mt-4 mb-2"><strong>Special Instructions</strong></div>

    <div class="row toileting_instructions">
        <p>{{ $client->careDetails['toileting_instructions'] }}</p>
    </div>

    <div class="row mt-4 mb-2"><strong>Bathing</strong></div>

    <div class="row bathing">
        @php  $checked = in_array('partial', $client->careDetails['bathing']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Partial</span>

        @php  $checked = in_array('shower', $client->careDetails['bathing']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Shower</span>

        @php  $checked = in_array('shower_chair', $client->careDetails['bathing']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Shower Chair</span>

        @php  $checked = in_array('complete', $client->careDetails['bathing']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Complete</span>

        @php  $checked = in_array('sponge_bath', $client->careDetails['bathing']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Sponge Bath</span>

        @php  $checked = in_array('bed_bath', $client->careDetails['bathing']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Bed Bath</span>

        @php  $checked = in_array('tub', $client->careDetails['bathing']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Tub</span>

        @php  $checked = in_array('sink', $client->careDetails['bathing']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Sink</span>
    </div>

    <div class="row mt-4 mb-2"><strong>Frequency</strong></div>

    <div class="row toileting_instructions">
        <p>{{ $client->careDetails['bathing_frequency'] }}</p>
    </div>

    <div class="row mt-4 mb-2"><strong>Instructions</strong></div>

    <div class="row toileting_instructions">
        <p>{{ $client->careDetails['bathing_instructions'] }}</p>
    </div>

    <div class="row mt-4 mb-2"><strong>Vision</strong></div>

    <div class="row vision">
        @php  $checked = 'right' == $client->careDetails['vision'] ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>R Eye</span>

        @php  $checked = 'left' == $client->careDetails['vision'] ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>L Eye</span>

        @php  $checked = 'glasses' == $client->careDetails['vision'] ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Glasses</span>

        @php  $checked = 'normal' == $client->careDetails['vision'] ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Normal</span>

        @php  $checked = 'peripheral' == $client->careDetails['vision'] ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Peripheral Only</span>

        @php  $checked = 'no_peripheral' == $client->careDetails['vision'] ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>No Peripheral Vision</span>

        @php  $checked = 'blind' == $client->careDetails['vision'] ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Blind</span>
    </div>

    <div class="row mt-4 mb-2"><strong>Hearing</strong></div>

    <div class="row hearing">
        @php  $checked = 'normal' == $client->careDetails['hearing'] ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Normal Hearing</span>

        @php  $checked = 'hard' == $client->careDetails['hearing'] ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Hard of Hearing</span>

        @php  $checked = 'hearing_aid' == $client->careDetails['hearing'] ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Hearing Aid</span>

        @php  $checked = 'deaf' == $client->careDetails['hearing'] ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Deaf</span>
    </div>

    <div class="row mt-4 mb-2"><strong>Special Instructions</strong></div>

    <div class="row hearing_instructions">
        <p>{{ $client->careDetails['hearing_instructions'] }}</p>
    </div>

    <div class="row mt-4 mb-2"><strong>Diet</strong></div>

    <div class="row diet">
        @php  $checked = in_array('normal', $client->careDetails['diet']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Normal</span>

        @php  $checked = in_array('liquid', $client->careDetails['diet']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Liquids Only</span>

        @php  $checked = in_array('encourage_fluids', $client->careDetails['diet']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Encourage Fluids</span>

        @php  $checked = in_array('lunch', $client->careDetails['diet']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Prepare &amp; serve lunch</span>

        @php  $checked = in_array('diabetic', $client->careDetails['diet']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Diabetic</span>

        @php  $checked = in_array('assist_meals', $client->careDetails['diet']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Assist with meals</span>

        @php  $checked = in_array('limit_fluids', $client->careDetails['diet']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Normal</span>

        @php  $checked = in_array('snacks', $client->careDetails['diet']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Prepare &amp; serve snacks</span>

        <br>

        @php  $checked = in_array('low_sodium', $client->careDetails['diet']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Low Sodium</span>

        @php  $checked = in_array('assist_feeding', $client->careDetails['diet']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Feeding Assistance</span>

        @php  $checked = in_array('breakfast', $client->careDetails['diet']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Prepare &amp; serve breakfast</span>

        @php  $checked = in_array('dinner', $client->careDetails['diet']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Prepare &amp; serve dinner</span>
    </div>

    <div class="row mt-4 mb-2"><strong>Special Likes and Dislikes</strong></div>

    <div class="row diet_likes">
        <p>{{ $client->careDetails['diet_likes'] }}</p>
    </div>

    <div class="row mt-4 mb-2"><strong>Feeding Instructions</strong></div>

    <div class="row feeding_instructions">
        <p>{{ $client->careDetails['feeding_instructions'] }}</p>
    </div>

    <div class="row mt-4 mb-2"><strong>Skin Care</strong></div>

    <div class="row skin">
        @php  $checked = in_array('moisturizer', $client->careDetails['skin']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Moisturizer</span>

        @php  $checked = in_array('intact', $client->careDetails['skin']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Skin Intact</span>

        @php  $checked = in_array('powder', $client->careDetails['skin']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Powder</span>

        @php  $checked = in_array('breakdown', $client->careDetails['skin']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Skin Breakdown</span>

        @php  $checked = in_array('preventative', $client->careDetails['skin']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Preventative</span>
    </div>

    <div class="row mt-4 mb-2"><strong>List and Skin Conditions</strong></div>

    <div class="row skin_conditions">
        <p>{{ $client->careDetails['skin_conditions'] }}</p>
    </div>

    <div class="row mt-4 mb-2"><strong>Hair Care</strong></div>

    <div clas="row hair">
        @php  $checked = 'dry' == $client->careDetails['hair'] ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Wash &amp; dry</span>

        @php  $checked = 'set' == $client->careDetails['hair'] ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Wash &amp; set</span>

        @php  $checked = 'brush' == $client->careDetails['hair'] ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Brush</span>

        @php  $checked = 'hair_dresser' == $client->careDetails['hair'] ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Hair Dresser</span>
    </div>

    <div class="row mt-4 mb-2"><strong>Frequency</strong></div>

    <div class="row hair_frequency">
        <p>{{ $client->careDetails['hair_frequency'] }}</p>
    </div>

    <div class="row mt-4 mb-2"><strong>Oral Care</strong></div>

    <div class="row oral">
        @php  $checked = in_array('brush', $client->careDetails['oral']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Brush and Floss</span>

        @php  $checked = in_array('dentures', $client->careDetails['oral']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Denture Care</span>
    </div>

    <div class="row mt-4 mb-2"><strong>Shaving</strong></div>

    <div class="row shaving">
        @php  $checked = 'yes' == $client->careDetails['shaving'] ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Yes</span>

        @php  $checked = 'no' == $client->careDetails['shaving'] ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>No</span>

        @php  $checked = 'self' == $client->careDetails['shaving'] ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Self</span>

        @php  $checked = 'assisted' == $client->careDetails['shaving'] ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Caregiver assistance</span>
    </div>

    <div class="row mt-4 mb-2"><strong>Special Instructions</strong></div>

    <div class="row shaving_instructions">
        <p>{{ $client->careDetails['shaving_instructions'] }}</p>
    </div>

    <div class="row mt-4 mb-2"><strong>Nail Care</strong></div>

    <div class="row nails">
        @php  $checked = in_array('clean', $client->careDetails['nails']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Clean</span>

        @php  $checked = in_array('file', $client->careDetails['nails']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>File</span>

        @php  $checked = in_array('polish', $client->careDetails['nails']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Polish</span>
    </div>

    <div class="row mt-4 mb-2"><strong>Dressing</strong></div>

    <div class="row dressing">
        @php  $checked = in_array('self', $client->careDetails['dressing']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Self dress</span>

        @php  $checked = in_array('clothes', $client->careDetails['dressing']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Help select clothes</span>

        @php  $checked = in_array('assist', $client->careDetails['dressing']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Assist with dressing</span>
    </div>

    <div class="row mt-4 mb-2"><strong>Special Instructions</strong></div>

    <div class="row dressing_instructions">
        <p>{{ $client->careDetails['dressing_instructions'] }}</p>
    </div>

    <div class="row mt-4 mb-2"><strong>Housekeeping</strong></div>

    <div class="row housekeeping">
        @php  $checked = in_array('vacuuming', $client->careDetails['housekeeping']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Vacuuming</span>

        @php  $checked = in_array('dusting', $client->careDetails['housekeeping']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Dusting</span>

        @php  $checked = in_array('trash', $client->careDetails['housekeeping']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Trash Removal</span>

        @php  $checked = in_array('make_bed', $client->careDetails['housekeeping']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Make Bed</span>

        @php  $checked = in_array('bed_linens', $client->careDetails['housekeeping']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Change Bed Linens</span>

        @php  $checked = in_array('laundry', $client->careDetails['housekeeping']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Laundry</span>

        @php  $checked = in_array('clean_bathroom', $client->careDetails['housekeeping']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Clean bathroom after use</span>

        <br>

        @php  $checked = in_array('change_linens', $client->careDetails['housekeeping']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Change bathroom linens</span>

        @php  $checked = in_array('dishes', $client->careDetails['housekeeping']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Dishes</span>

        @php  $checked = in_array('clean_kitchen', $client->careDetails['housekeeping']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Clean up kitchen after use</span>

        @php  $checked = in_array('mop', $client->careDetails['housekeeping']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Damp mop floors</span>

        @php  $checked = in_array('other', $client->careDetails['housekeeping']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Other</span>
    </div>

    <div class="row mt-4 mb-2"><strong>Special Instructions</strong></div>

    <div class="row housekeeping_instructions">
        <p>{{ $client->careDetails['housekeeping_instructions'] }}</p>
    </div>

    <div class="row mt-4 mb-2"><strong>Shopping/Errands</strong></div>

    <div class="row errands">
        @php  $checked = in_array('drives', $client->careDetails['errands']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Drives</span>

        @php  $checked = in_array('authorized_take_out', $client->careDetails['errands']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Caregiver may take out</span>

        @php  $checked = in_array('call_take_out', $client->careDetails['errands']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Call before taking out</span>

        @php  $checked = in_array('has_waiver', $client->careDetails['errands']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Waiver of liability on file</span>

        @php  $checked = in_array('taxi', $client->careDetails['errands']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Accompany on taxi/bus</span>

        @php  $checked = in_array('caregiver_car', $client->careDetails['errands']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Run errands in caregiver's car</span>

        <br>

        @php  $checked = in_array('client_car', $client->careDetails['errands']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Run errands in client's car</span>
    </div>

    <div class="row mt-4 mb-2"><strong>Supplies Available</strong></div>

    <div class="row supplies">
        @php  $checked = in_array('gloves', $client->careDetails['supplies']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Gloves</span>

        @php  $checked = in_array('sanitizer', $client->careDetails['supplies']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Sanitizer</span>

        @php  $checked = in_array('caregiver', $client->careDetails['supplies']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Caregiver must bring own</span>

        @php  $checked = in_array('other', $client->careDetails['supplies']) ? 'checked' : ''; @endphp
        <input type="checkbox" {{$checked}}>
        <span>Other</span>
    </div>

    <div class="row mt-4 mb-2"><strong>Special Instructions</strong></div>

    <div class="row supplies_instructions">
        <p>{{ $client->careDetails['supplies_instructions'] }}</p>
    </div>

    <div class="row mt-4 mb-2"><strong>Comments</strong></div>

    <div class="row comments">
        <p>{{ $client->careDetails['comments'] }}</p>
    </div>

    <div class="row mt-4 mb-2"><strong>Special Instructions</strong></div>

    <div class="row instructions">
        <p>{{ $client->careDetails['instructions'] }}</p>
    </div>
@endsection
