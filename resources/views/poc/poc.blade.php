<html>
    <head>
        <style>
            span.cls_002{font-family:Arial,serif;font-size:7.0px;color:rgb(0,0,0);font-weight:normal;font-style:normal;text-decoration: none}
            div.cls_002{font-family:Arial,serif;font-size:7.0px;color:rgb(0,0,0);font-weight:normal;font-style:normal;text-decoration: none}
            span.cls_003{font-family:Arial,serif;font-size:11.0px;color:rgb(0,0,0);font-weight:bold;font-style:normal;text-decoration: none}
            div.cls_003{font-family:Arial,serif;font-size:11.0px;color:rgb(0,0,0);font-weight:bold;font-style:normal;text-decoration: none}
            span.cls_004{font-family:Arial,serif;font-size:7.8px;color:rgb(0,0,0);font-weight:normal;font-style:normal;text-decoration: none; display:inline-block; width: 100%;}
            div.cls_004{font-family:Arial,serif;font-size:7.8px;color:rgb(0,0,0);font-weight:normal;font-style:normal;text-decoration: none; width: 100px;}
            span.cls_005{font-family:Arial,serif;font-size:6.0px;color:rgb(0,0,0);font-weight:normal;font-style:normal;text-decoration: none;}
            div.cls_005{font-family:Arial,serif;font-size:6.0px;color:rgb(0,0,0);font-weight:normal;font-style:normal;text-decoration: none}
            div.cls_005 span.selected{font-size: 11px;position:absolute;color:blue;left:-6px;top:0px;}
            span.cls_005.gender-label{position:relative;left: 8px;}
            span.cls_006{font-family:Arial,serif;font-size:7.8px;color:rgb(0,0,0);font-weight:normal;font-style:normal;text-decoration: none;display:inline-block; width: 100%;}
            div.cls_006{font-family:Arial,serif;font-size:7.8px;color:rgb(0,0,0);font-weight:normal;font-style:normal;text-decoration: none}

            .checked{color:blue;position:absolute;left:-11px;top:0px;font-size:7.8px;}
            .prognosis{padding-left:6px;}

        </style>
    </head>
    <body>
        <div style="position:absolute;left:50%;margin-left:-306px;top:0px;height:792px;width:660px; border-style:outset;overflow:hidden">
            <div style="position:absolute;left:0px;top:-1px;" class="img_container">
                <img src="{{$image}}" style="height:792px;width:611px;">
            </div>

            <div style="position:absolute;left:11.50px;top:14.12px" class="cls_002"><span class="cls_002">Department of Health and Human Services</span></div>
            <div style="position:absolute;left:491.56px;top:14.12px" class="cls_002"><span class="cls_002">Form Approved</span></div>
            <div style="position:absolute;left:11.50px;top:22.04px" class="cls_002"><span class="cls_002">Centers for Medicare & Medicaid Services</span></div>
            <div style="position:absolute;left:491.56px;top:22.58px" class="cls_002"><span class="cls_002">OMB No. 0938-0357</span></div>
            <div style="position:absolute;left:146.14px;top:30.51px" class="cls_003"><span class="cls_003">HOME HEALTH CERTIFICATION AND PLAN OF CARE</span></div>

            <div style="position:absolute;left:11.50px;top:43.90px" class="cls_004">
                <span class="cls_004">1. Patient HI Claim No.</span>
                <span class="cls_004" style="width:200px;">{{ $client->hic }}</span>
            </div>

            <div style="position:absolute;left:139.47px;top:43.90px" class="cls_004">
                <span class="cls_004">2. Start Of Care Date </span>
                <span class="cls_004">{{ $client->start_of_care }}</span>
            </div>

            <div style="position:absolute;left:223.52px;top:43.90px" class="cls_004">
                <span class="cls_004">3. Certification Period</span>
            </div>

            <div style="position:absolute;left:409.48px;top:43.90px" class="cls_004">
                <span class="cls_004">4. Medical Record No.</span>
                <span class="cls_004">{{ $client->skilledNursingPoc["medical_record_number"] }}</span>
            </div>

            <div style="position:absolute;left:511.54px;top:43.90px" class="cls_004">
                <span class="cls_004">5. Provider No.</span>
                <span class="cls_004">{{ $client->skilledNursingPoc['provider_number'] }}</span>
            </div>

            <div style="position:absolute;left:233.44px;top:57.40px" class="cls_004">
                <span class="cls_004">From:{{ $client->skilledNursingPoc['certification_start'] }}</span>
            </div>

            <div style="position:absolute;left:311.54px;top:57.40px" class="cls_004">
                <span class="cls_004">To:{{ $client->skilledNursingPoc['certification_end'] }}</span>
            </div>

            <div style="position:absolute;left:11.50px;top:68.92px;width:50%;" class="cls_004">
                <span class="cls_004">6. Patient's Name and Address</span>
                {{ $client->nameLastFirst() }}<br>
                @if($client->getBillingAddress())
                {{ $client->getBillingAddress()->getStreetAddressAttribute() }}<br>
                {{ $client->getBillingAddress()->getCityStateZipAttribute() }}
                @endif
            </div>

            <div style="position:absolute;left:295.52px;top:68.92px;width:50%;" class="cls_004">
                <span class="cls_004">7. Provider's Name, Address and Telephone Number</span>
                    {{ $client->business->getBillingName() }}<br>
                @if($client->business->getStreetAddressAttribute())
                    {{ $client->business->getStreetAddressAttribute() }}<br>
                    {{ $client->business->getCityStateZipAttribute() }}
                @elseif($client->business->businessChain->getStreetAddressAttribute())
                    {{ $client->business->businessChain->getStreetAddressAttribute() }}<br>
                    {{ $client->business->businessChain->getCityStateZipAttribute() }}
                @endif

            </div>

            <div style="position:absolute;left:13.79px;top:129.76px" class="cls_004">
                <span class="cls_004">8. Date of Birth: {{ $client->date_of_birth }}</span>
            </div>

            <div style="position:absolute;left:200.50px;top:129.40px" class="cls_004"><span class="cls_004">9. Sex</span></div>

            <div style="position:absolute;left:240.60px;top:128.88px" class="cls_005">
                @if($client->gender === "M")
                    <span class="selected">X</span>
                @else
                    <span class="selected">&nbsp;</span>
                @endif
                <span class="cls_005 gender-label">M</span>
            </div>

            <div style="position:absolute;left:270.28px;top:128.70px" class="cls_005">
                @if($client->gender === "F")
                    <span class="selected">X</span>
                @else
                    <span class="selected">&nbsp;</span>
                @endif
                <span class="cls_005 gender-label">F</span>
            </div>

            <div style="position:absolute;left:296.44px;top:128.86px" class="cls_004"><span class="cls_004">10. Medications:</span></div>
            <div style="position:absolute;left:359.14px;top:128.86px;width:50%;" class="cls_004"><span class="cls_004">Dose/Frequency/Route (N)ew (C)hanged</span>
                @foreach($client->medications as $medication)
                    <span class="cls_005"  style="width:350px;position:relative;left:-48px;">
                        {{ $medication->dose }} - {{ $medication->frequency }} - {{ $medication->route }} - {{ $medication->new_changed }}
                    </span>
                    <br>
                @endforeach
            </div>

            <div style="position:absolute;left:11.50px;top:140.92px" class="cls_004">
                <span class="cls_004">11. ICD-9-CM</span>
                <span class="cls_004">{{ $client->skilledNursingPoc['principal_diagnosis_icd_cm'] }}</span>
            </div>
            <div style="position:absolute;left:68.56px;top:140.92px" class="cls_004">
                <span class="cls_004">Principal Diagnosis</span>
                <span class="cls_004">{{ $client->skilledNursingPoc['principal_diagnosis'] }}</span>
            </div>
            <div style="position:absolute;left:237.04px;top:140.92px" class="cls_004">
                <span class="cls_004">Date</span>
                <span class="cls_004">{{ $client->skilledNursingPoc['principal_diagnosis_date'] }}</span>
            </div>

            <div style="position:absolute;left:11.50px;top:164.86px" class="cls_004">
                <span class="cls_004">12. ICD-9-CM</span>
                <span class="cls_004">{{ $client->skilledNursingPoc['surgical_procedure_icd_cm'] }}</span>
            </div>
            <div style="position:absolute;left:68.56px;top:164.86px" class="cls_004">
                <span class="cls_004">Surgical Procedure</span>
                <span class="cls_004">{{ $client->skilledNursingPoc['surgical_procedure'] }}</span>
            </div>
            <div style="position:absolute;left:237.04px;top:164.86px" class="cls_004">
                <span class="cls_004">Date</span>
                <span class="cls_004">{{ $client->skilledNursingPoc['surgical_procedure_date'] }}</span>
            </div>

            <div style="position:absolute;left:11.50px;top:189.34px" class="cls_004">
                <span class="cls_004">13. ICD-9-CM</span>
                <span class="cls_004">{{ $client->skilledNursingPoc['other_diagnosis_icd_cm'] }}</span>
                <span class="cls_004">{{ $client->skilledNursingPoc['other_diagnosis_icd_cm1'] }}</span>
                <span class="cls_004">{{ $client->skilledNursingPoc['other_diagnosis_icd_cm2'] }}</span>
            </div>
            <div style="position:absolute;left:68.56px;top:189.34px" class="cls_004">
                <span class="cls_004">Other Pertinent Diagnoses</span>
                <span class="cls_004">{{ $client->skilledNursingPoc['other_diagnosis'] }}</span>
                <span class="cls_004">{{ $client->skilledNursingPoc['other_diagnosis1'] }}</span>
                <span class="cls_004">{{ $client->skilledNursingPoc['other_diagnosis2'] }}</span>
            </div>
            <div style="position:absolute;left:237.04px;top:189.34px" class="cls_004">
                <span class="cls_004">Date</span>
                <span class="cls_004">{{ $client->skilledNursingPoc['other_diagnosis_date'] }}</span>
                <span class="cls_004">{{ $client->skilledNursingPoc['other_diagnosis_date1'] }}</span>
                <span class="cls_004">{{ $client->skilledNursingPoc['other_diagnosis_date2'] }}</span>
            </div>

            <div style="position:absolute;left:11.50px;top:249.82px" class="cls_004">
                <span class="cls_004">14. DME and Supplies</span>
                <span class="cls_004"  style="width:100%;">{{ $client->careDetails->supplies_as_string }}</span>
            </div>

            <div style="position:absolute;left:299.50px;top:249.82px" class="cls_004">
                <span class="cls_004">15. Safety Measures:</span>
                <span class="cls_004" style="width:300px;position:absolute;left:0px;">{{ $client->careDetails->safety_measures_as_string }}</span>
            </div>

            <div style="position:absolute;left:11.50px;top:272.32px" class="cls_004">
                <span class="cls_004" style="width:300px;">16. Nutritional Req {{ $client->careDetails->diet_as_string }}.</span>
            </div>
            <div style="position:absolute;left:298.96px;top:272.86px" class="cls_004">
                <span class="cls_004" style="width:300px;">17. Allergies:{{ $client->careDetails->allergies }}</span>
            </div>

            <div style="position:absolute;left:11.50px;top:285.82px;width:45%;" class="cls_004"><span class="cls_004">18.A. Functional Limitations</span></div>

            <div style="position:absolute;left:298.96px;top:284.74px;width:50%;" class="cls_004"><span class="cls_004">18.B. Activities Permitted</span></div>


            <div style="position:absolute;left:46.24px;top:296.82px" class="cls_005">
                <span class="cls_005">
                    @if( in_array('amputation', $client->careDetails['functional'])  )
                        <span class="checked">X&nbsp;</span>
                    @endif
                    Amputation
                </span>
            </div>

            <div style="position:absolute;left:161.82px;top:296.82px" class="cls_005">
                @if( in_array('paralysis', $client->careDetails['functional'])  )
                    <span class="checked">X&nbsp;</span>
                @endif
                <span class="cls_005">Paralysis</span>
            </div>

            <div style="position:absolute;left:233.24px;top:297.54px" class="cls_005">
                @if( in_array('blind', $client->careDetails['functional'])  )
                    <span class="checked">X&nbsp;</span>
                @endif
                <span class="cls_005">Legally Blind</span>
            </div>

            <div style="position:absolute;left:327.56px;top:296.82px" class="cls_005">
                @if( in_array('bedrest', $client->careDetails['functional'])  )
                    <span class="checked">X&nbsp;</span>
                @endif
                <span class="cls_005">Complete Bedrest</span>
            </div>

            <div style="position:absolute;left:427.66px;top:296.82px" class="cls_005">
                @if( in_array('partial_weight_bearing', $client->careDetails['mobility'])  )
                    <span class="checked">X&nbsp;</span>
                @endif
                <span class="cls_005">Partial Weight Bearing</span>
            </div>

            <div style="position:absolute;left:528.26px;top:296.82px" class="cls_005">
                @if( in_array('wheelchair', $client->careDetails['mobility'])  )
                    <span class="checked">X&nbsp;</span>
                @endif
                <span class="cls_005">Wheelchair</span>
            </div>
            <div style="position:absolute;left:20.32px;top:296.98px" class="cls_004"><span class="cls_004">1</span></div>
            <div style="position:absolute;left:135.70px;top:296.44px" class="cls_004"><span class="cls_004">5</span></div>
            <div style="position:absolute;left:207.34px;top:297.16px" class="cls_004"><span class="cls_004">9</span></div>
            <div style="position:absolute;left:301.48px;top:296.80px" class="cls_004"><span class="cls_004">1</span></div>
            <div style="position:absolute;left:401.74px;top:296.44px" class="cls_004"><span class="cls_004">6</span></div>
            <div style="position:absolute;left:502.18px;top:296.44px" class="cls_004"><span class="cls_004">A</span></div>

            <div style="position:absolute;left:161.78px;top:308.88px" class="cls_005">
                @if( in_array('endurance', $client->careDetails['functional'])  )
                    <span class="checked">X&nbsp;</span>
                @endif
                <span class="cls_005">Endurance</span>
            </div>

            <div style="position:absolute;left:232.72px;top:308.70px" class="cls_005">
                @if( in_array('dyspnea', $client->careDetails['functional'])  )
                    <span class="checked">X&nbsp;</span>

                @endif
                <span class="cls_005">Dyspnea With</span>
            </div>

            <div style="position:absolute;left:427.64px;top:308.88px" class="cls_005">
                @if( in_array('independent_at_home', $client->careDetails['mobility'])  )
                    <span class="checked">X&nbsp;</span>
                @endif
                <span class="cls_005">Independent At Home</span>
            </div>

            <div style="position:absolute;left:527.72px;top:309.98px" class="cls_005">
                @if( in_array('walker', $client->careDetails['mobility'])  )
                    <span class="checked">X&nbsp;</span>
                @endif
                <span class="cls_005">Walker</span>
            </div>

            <div style="position:absolute;left:20.32px;top:308.86px" class="cls_004"><span class="cls_004">2</span></div>

            <div style="position:absolute;left:45.36px;top:309.78px" class="cls_005">
                @if( in_array('incontinence', $client->careDetails['functional'])  )
                    <span class="checked">X&nbsp;</span>
                @endif
                <span class="cls_005">Bowel/Bladder (Incontinence)</span>
            </div>

            <div style="position:absolute;left:135.70px;top:308.32px" class="cls_004"><span class="cls_004">6</span></div>
            <div style="position:absolute;left:207.34px;top:309.22px" class="cls_004"><span class="cls_004">A</span></div>
            <div style="position:absolute;left:301.48px;top:308.86px" class="cls_004"><span class="cls_004">2</span></div>

            <div style="position:absolute;left:326.52px;top:309.78px" class="cls_005">
                @if( in_array('bedrest_brp', $client->careDetails['mobility'])  )
                    <span class="checked">X&nbsp;</span>
                @endif
                <span class="cls_005">Bedrest BRP</span>
            </div>

            <div style="position:absolute;left:401.74px;top:308.32px" class="cls_004"><span class="cls_004">7</span></div>
            <div style="position:absolute;left:502.18px;top:308.32px" class="cls_004"><span class="cls_004">B</span></div>

            <div style="position:absolute;left:232.72px;top:314.82px" class="cls_005">
                <span class="cls_005">Minimal Exertion</span>
            </div>

            <div style="position:absolute;left:45.88px;top:321.30px" class="cls_005">
                @if( in_array('contracture', $client->careDetails['functional'])  )
                    <span class="checked">X&nbsp;</span>
                @endif
                <span class="cls_005">Contracture</span>
            </div>

            <div style="position:absolute;left:161.24px;top:320.76px" class="cls_005">
                @if( in_array('ambulation', $client->careDetails['functional'])  )
                    <span class="checked">X&nbsp;</span>
                @endif
                <span class="cls_005">Ambulation</span>
            </div>

            <div style="position:absolute;left:232.72px;top:321.48px" class="cls_005">
                @if( in_array('other', $client->careDetails['functional'])  )
                    <span class="checked">X&nbsp;</span>
                @endif
                <span class="cls_005">Other (Specify)</span>
            </div>

            <div style="position:absolute;left:327.04px;top:321.30px" class="cls_005">
                @if( in_array('up_as_tolerated', $client->careDetails['mobility'])  )
                    <span class="checked">X&nbsp;</span>
                @endif
                <span class="cls_005">Up As Tolerated</span>
            </div>

            <div style="position:absolute;left:427.28px;top:320.76px" class="cls_005">
                @if( in_array('crutches', $client->careDetails['mobility'])  )
                    <span class="checked">X&nbsp;</span>
                @endif
                <span class="cls_005">Crutches</span>
            </div>

            <div style="position:absolute;left:527.76px;top:320.76px" class="cls_005">
                @if( in_array('none', $client->careDetails['mobility'])  )
                    <span class="checked">X&nbsp;</span>
                @endif
                <span class="cls_005">No Restrictions</span>
            </div>

            <div style="position:absolute;left:20.32px;top:320.92px" class="cls_004"><span class="cls_004">3</span></div>
            <div style="position:absolute;left:135.70px;top:320.38px" class="cls_004"><span class="cls_004">7</span></div>

            <div style="position:absolute;left:207.34px;top:321.10px" class="cls_004">
                <span class="cls_004">B</span>
                <span class="cls_004 functional_other">{{ $client->careDetails['functional_other'] }}</span>
            </div>
            <div style="position:absolute;left:301.48px;top:320.92px" class="cls_004"><span class="cls_004">3</span></div>
            <div style="position:absolute;left:401.74px;top:320.38px" class="cls_004"><span class="cls_004">8</span></div>
            <div style="position:absolute;left:502.18px;top:320.38px" class="cls_004"><span class="cls_004">C</span></div>

            <div style="position:absolute;left:45.86px;top:333.36px" class="cls_005">
                @if( in_array('hearing', $client->careDetails['functional'])  )
                    <span class="checked">X&nbsp;</span>
                @endif
                <span class="cls_005">Hearing</span>
            </div>

            <div style="position:absolute;left:161.25px;top:333.72px" class="cls_005">
                @if( in_array('speech', $client->careDetails['functional'])  )
                    <span class="checked">X&nbsp;</span>
                @endif
                <span class="cls_005">Speech</span>
            </div>

            <div style="position:absolute;left:327.06px;top:333.36px" class="cls_005">
                @if( in_array('assist_transfers', $client->careDetails['mobility'])  )
                    <span class="checked">X&nbsp;</span>
                @endif
                <span class="cls_005">Transfer Bed/Chair</span>
            </div>

            <div style="position:absolute;left:427.30px;top:332.82px" class="cls_005">
                @if( in_array('cane', $client->careDetails['mobility'])  )
                    <span class="checked">X&nbsp;</span>
                @endif
                <span class="cls_005">Cane</span>
            </div>

            <div style="position:absolute;left:527.73px;top:333.00px" class="cls_005">
                @if( in_array('other', $client->careDetails['mobility'])  )
                    <span class="checked">X&nbsp;</span>
                @endif
                <span class="cls_005">Other (Specify)</span>
            </div>

            <div style="position:absolute;left:20.32px;top:332.98px" class="cls_004"><span class="cls_004">4</span></div>
            <div style="position:absolute;left:135.70px;top:333.34px" class="cls_004"><span class="cls_004">8</span></div>
            <div style="position:absolute;left:301.48px;top:332.98px" class="cls_004"><span class="cls_004">4</span></div>
            <div style="position:absolute;left:401.74px;top:332.44px" class="cls_004"><span class="cls_004">9</span></div>

            <div style="position:absolute;left:502.18px;top:332.62px" class="cls_004">
                <span class="cls_004">D</span>
                <span class="cls_004 mobility_other">{{ $client->careDetails['mobility_other'] }}</span>
            </div>

            <div style="position:absolute;left:301.48px;top:344.86px" class="cls_004"><span class="cls_004">5</span></div>

            <div style="position:absolute;left:327.03px;top:345.24px" class="cls_005">
                @if( in_array('exercises_prescribed', $client->careDetails['mobility'])  )
                    <span class="checked">X&nbsp;</span>
                @endif
                <span class="cls_005">Exercises Prescribed</span>
            </div>

            <div style="position:absolute;left:11.50px;top:357.64px" class="cls_004"><span class="cls_004">19. Mental Status:</span></div>
            <div style="position:absolute;left:136.06px;top:356.74px" class="cls_004"><span class="cls_004">1</span></div>

            <div style="position:absolute;left:162.13px;top:357.12px" class="cls_005">
                @if( in_array('oriented', $client->careDetails['mental_status'])  )
                    <span class="checked">X&nbsp;</span>
                @endif
                <span class="cls_005">Oriented</span>
            </div>
            <div style="position:absolute;left:207.34px;top:356.74px" class="cls_004"><span class="cls_004">3</span></div>

            <div style="position:absolute;left:233.27px;top:357.12px" class="cls_005">
                @if( in_array('forgetful', $client->careDetails['mental_status'])  )
                    <span class="checked">X&nbsp;</span>
                @endif
                <span class="cls_005">Forgetful</span>
            </div>
            <div style="position:absolute;left:300.94px;top:356.74px" class="cls_004"><span class="cls_004">5</span></div>

            <div style="position:absolute;left:327.03px;top:357.12px" class="cls_005">
                @if( in_array('disoriented', $client->careDetails['mental_status'])  )
                    <span class="checked">X&nbsp;</span>
                @endif
                <span class="cls_005">Disoriented</span>
            </div>
            <div style="position:absolute;left:401.92px;top:357.82px" class="cls_004"><span class="cls_004">7</span></div>

            <div style="position:absolute;left:427.99px;top:358.38px" class="cls_005">
                @if( in_array('agitated', $client->careDetails['mental_status'])  )
                    <span class="checked">X&nbsp;</span>
                @endif
                <span class="cls_005">Agitated</span>
            </div>

            <div style="position:absolute;left:136.06px;top:368.98px" class="cls_004"><span class="cls_004">2</span></div>

            <div style="position:absolute;left:161.62px;top:369.54px" class="cls_005">
                @if( in_array('comatose', $client->careDetails['mental_status'])  )
                    <span class="checked">X&nbsp;</span>
                @endif
                <span class="cls_005">Comatose</span>
            </div>

            <div style="position:absolute;left:207.34px;top:369.34px" class="cls_004"><span class="cls_004">4</span></div>

            <div style="position:absolute;left:232.34px;top:369.72px" class="cls_005">
                @if( in_array('depressed', $client->careDetails['mental_status'])  )
                    <span class="checked">X&nbsp;</span>
                @endif
                <span class="cls_005">Depressed</span>
            </div>
            <div style="position:absolute;left:300.94px;top:368.98px" class="cls_004"><span class="cls_004">6</span></div>

            <div style="position:absolute;left:326.51px;top:369.00px" class="cls_005">
                @if( in_array('lethargic', $client->careDetails['mental_status'])  )
                    <span class="checked">X&nbsp;</span>
                @endif
                <span class="cls_005">Lethargic</span>
            </div>

            <div style="position:absolute;left:401.92px;top:368.98px" class="cls_004"><span class="cls_004">8</span></div>

            <div style="position:absolute;left:427.47px;top:369.36px" class="cls_005">
                @if( in_array('other', $client->careDetails['mental_status'])  )
                    <span class="checked">X&nbsp;</span>
                @endif
                <span class="cls_005">Other</span>
            </div>
            <div style="position:absolute;left:11.50px;top:381.58px" class="cls_004"><span class="cls_004">20. Prognosis:</span></div>
            <div style="position:absolute;left:135.52px;top:381.22px" class="cls_004"><span class="cls_004">1</span></div>

            <div style="position:absolute;left:163.16px;top:381.22px" class="cls_004">
                <span class="cls_004 prognosis">
                    @if( 'poor' == $client->careDetails['prognosis'] )
                        <span class="checked">X&nbsp;</span>
                    @endif
                    Poor
                </span>
            </div>

            <div style="position:absolute;left:207.34px;top:381.22px" class="cls_004"><span class="cls_004">2</span></div>

            <div style="position:absolute;left:234.10px;top:381.76px" class="cls_004">
                <span class="cls_004 prognosis">
                    @if( 'guarded' == $client->careDetails['prognosis'] )
                        <span class="checked">X&nbsp;</span>
                    @endif
                    Guarded
                </span>
            </div>

            <div style="position:absolute;left:300.76px;top:380.50px" class="cls_004"><span class="cls_004">3</span></div>

            <div style="position:absolute;left:328.61px;top:380.50px" class="cls_004">
                <span class="cls_004 prognosis">
                    @if( 'fair' == $client->careDetails['prognosis'] )
                        <span class="checked">X&nbsp;</span>
                    @endif
                    Fair
                </span>
            </div>

            <div style="position:absolute;left:401.56px;top:380.32px" class="cls_004"><span class="cls_004">4</span></div>

            <div style="position:absolute;left:429.22px;top:380.32px" class="cls_004">

                <span class="cls_004 prognosis">
                    @if( 'good' == $client->careDetails['prognosis'] )
                        <span class="checked">X&nbsp;</span>
                    @endif
                    Good
                </span>
            </div>

            <div style="position:absolute;left:502.72px;top:380.32px" class="cls_004"><span class="cls_004">5</span></div>

            <div style="position:absolute;left:528.43px;top:380.32px" class="cls_004">
                <span class="cls_004 prognosis">
                    @if( 'excellent' == $client->careDetails['prognosis'] )
                        <span class="checked">X&nbsp;</span>
                    @endif
                    Excellent
                </span>
            </div>

            <div style="position:absolute;left:11.50px;top:392.20px;width:100%;" class="cls_004">
                <span class="cls_004">21. Orders for Discipline and Treatments (Specify Amount/Frequency/Duration)</span>
                {{ $client->skilledNursingPoc['orders'] }}
            </div>

            <div style="position:absolute;left:11.50px;top:579.58px;width:100%" class="cls_004">
                <span class="cls_004">22. Goals/Rehabilitation Potential/Discharge Plans</span>
                @foreach($client->goals as $goal)
                    {{$goal["question"]}}<br>
                @endforeach
            </div>
            <div style="position:absolute;left:11.50px;top:638.62px" class="cls_004"><span class="cls_004">23.</span></div>
            <div style="position:absolute;left:27.07px;top:638.62px;width:50%;" class="cls_004"><span class="cls_004">Nurse's Signature and Date of Verbal SOC Where Applicable:</span></div>
            <div style="position:absolute;left:410.74px;top:638.62px" class="cls_004"><span class="cls_004">25.</span></div>
            <div style="position:absolute;left:426.31px;top:638.62px;width:50%;" class="cls_004"><span class="cls_004">Date HHA Received Signed POT</span></div>
            <div style="position:absolute;left:11.50px;top:664.18px" class="cls_004"><span class="cls_004">24.</span></div>
            <div style="position:absolute;left:27.07px;top:664.18px;width:50%;" class="cls_004">
                <span class="cls_004">Physician's Name and Address</span>
                {{ $client->skilledNursingPoc['physician_name'] }}<br>
                {{ $client->skilledNursingPoc['physician_address'] }}<br>
                {{ $client->skilledNursingPoc['physician_phone'] }}
            </div>
            <div style="position:absolute;left:295.54px;top:663.64px" class="cls_004"><span class="cls_004">26.</span></div>
            <div style="position:absolute;left:309.94px;top:663.79px;width:350px;" class="cls_006"><span class="cls_006">I certify/recertify that this patient is confined to his/her home and needs</span></div>
            <div style="position:absolute;left:309.94px;top:673.51px;width:350px;" class="cls_006"><span class="cls_006">intermittent skilled nursing care, physical therapy and/or speech therapy or</span></div>
            <div style="position:absolute;left:309.94px;top:683.05px;width:350px;" class="cls_006"><span class="cls_006">continues to need occupational therapy. The patient is under my care, and I have</span></div>
            <div style="position:absolute;left:309.94px;top:692.59px;width:350px;" class="cls_006"><span class="cls_006">authorized the services on this plan of care and will periodically review the plan.</span></div>
            <div style="position:absolute;left:11.50px;top:722.86px" class="cls_004"><span class="cls_004">27.</span></div>

            <div style="position:absolute;left:27.07px;top:722.86px;width:350px;" class="cls_004">
                <span class="cls_004">Attending Physician's Signature and Date Signed</span>
            </div>

            <div style="position:absolute;left:296.44px;top:722.86px;width:350px;" class="cls_004"><span class="cls_004">28. Anyone who misrepresents, falsifies, or conceals essential information</span></div>
            <div style="position:absolute;left:311.38px;top:732.40px;width:350px;" class="cls_004"><span class="cls_004">required for payment of Federal funds may be subject to fine, imprisonment,</span></div>
            <div style="position:absolute;left:311.38px;top:741.94px;width:350px;" class="cls_004"><span class="cls_004">or civil penalty under applicable Federal laws.</span></div>
            <div style="position:absolute;left:11.50px;top:764.26px;width:100%;" class="cls_004"><span class="cls_004">Form CMS-485 (C-3) (02-94) (Formerly HCFA-485) (Print Aligned)</span></div>

        </div><!-- close -->

    </body>
</html>
