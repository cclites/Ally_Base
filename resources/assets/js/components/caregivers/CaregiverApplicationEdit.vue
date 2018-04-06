<template>
    <b-container>
        <b-card title="Caregiver Application">
            <b-row>
                <b-col lg="4">
                    <b-form-group label="First Name*">
                        <b-form-input
                                v-model="form.first_name"
                                tabindex="1">
                        </b-form-input>
                    </b-form-group>
                    <b-form-group label="Date of Birth">
                        <b-form-input
                                type="text"
                                id="date_of_birth"
                                class="datepicker"
                                v-model="form.date_of_birth"
                                tabindex="4"
                        >
                        </b-form-input>
                    </b-form-group>
                </b-col>

                <b-col lg="4">
                    <b-form-group label="Middle Initial">
                        <b-form-input
                                v-model="form.middle_initial"
                                tabindex="2">
                        </b-form-input>
                    </b-form-group>
                    <b-form-group label="Social Security Number">
                        <b-form-input
                                v-model="form.ssn"
                                tabindex="5">
                        </b-form-input>
                    </b-form-group>
                </b-col>

                <b-col lg="4">
                    <b-form-group label="Last Name*">
                        <b-form-input
                                v-model="form.last_name"
                                tabindex="3">
                        </b-form-input>
                    </b-form-group>
                    <b-form-group label="Email*">
                        <b-form-input
                                v-model="form.email"
                                tabindex="6">
                        </b-form-input>
                    </b-form-group>
                </b-col>
            </b-row>

            <b-row>
                <b-col lg="6">
                    <b-form-group label="Address">
                        <b-form-input
                                v-model="form.address"
                                tabindex="7">
                        </b-form-input>
                    </b-form-group>
                </b-col>

                <b-col lg="6">
                    <b-form-group label="Address 2">
                        <b-form-input
                                v-model="form.address_2"
                                tabindex="8">
                        </b-form-input>
                    </b-form-group>
                </b-col>
            </b-row>

            <b-row>
                <b-col lg="4">
                    <b-form-group label="City">
                        <b-form-input
                                v-model="form.city"
                                tabindex="9">
                        </b-form-input>
                    </b-form-group>
                    <b-form-group label="Cell Phone*">
                        <b-form-input
                                v-model="form.cell_phone"
                                tabindex="12">
                        </b-form-input>
                    </b-form-group>
                </b-col>

                <b-col lg="4">
                    <b-form-group label="State">
                        <b-form-input
                                v-model="form.state"
                                tabindex="10">
                        </b-form-input>
                    </b-form-group>
                    <b-form-group label="Cell Phone Provider">
                        <b-form-input
                                v-model="form.cell_phone_provider"
                                tabindex="13">
                        </b-form-input>
                    </b-form-group>
                </b-col>

                <b-col lg="4">
                    <b-form-group label="Zip">
                        <b-form-input
                                v-model="form.zip"
                                tabindex="11">
                        </b-form-input>
                    </b-form-group>
                    <b-form-group label="Home Phone">
                        <b-form-input
                                v-model="form.home_phone"
                                tabindex="14">
                        </b-form-input>
                    </b-form-group>
                </b-col>
            </b-row>

            <b-row>
                <b-col lg="8">
                    <b-form-group label="Emergency Contact Name">
                        <b-form-input
                                v-model="form.emergency_contact_name">
                        </b-form-input>
                    </b-form-group>
                </b-col>
                <b-col lg="4">
                    <b-form-group label="Emergency Contact Phone">
                        <b-form-input
                                v-model="form.emergency_contact_phone">
                        </b-form-input>
                    </b-form-group>
                </b-col>
            </b-row>

            <b-row>
                <b-col lg="6">
                    <b-form-checkbox id="worked_here_before"
                                     v-model="form.worked_here_before"
                                     :value="1"
                                     :unchecked-value="0">
                        Have you worked for {{ business.name }} before?
                    </b-form-checkbox>
                </b-col>
                <b-col lg="6">
                    <b-form-group label="Which Location?" v-show="form.worked_here_before">
                        <b-form-input v-model="form.worked_before_location"></b-form-input>
                    </b-form-group>
                </b-col>
            </b-row>

            <b-row>
                <b-col lg="12">
                    <b-form-group label="Position applying for?" class="mt-2">
                        <b-form-radio-group id="caregiver_position_id" v-model="form.caregiver_position_id" name="radioSubComponent">
                            <b-form-radio v-for="position in positions" :value="position.id">{{ position.name }}</b-form-radio>
                        </b-form-radio-group>
                    </b-form-group>
                </b-col>
            </b-row>
            <!-- Availability -->
            <b-row>
                <b-col>
                    <div class="h5">Availability</div>
                </b-col>
            </b-row>
            <hr>
            <b-row>
                <b-col lg="6">
                    <b-form-group label="Preferred Start Date">
                        <b-form-input
                                type="text"
                                id="preferred_start_date"
                                class="datepicker"
                                v-model="form.preferred_start_date"
                        >
                        </b-form-input>
                    </b-form-group>
                    <b-form-group label="Preferred Days">
                        <b-form-checkbox-group id="preferred_days" v-model="form.preferred_days">
                            <b-form-checkbox v-for="day in days" :value="day">{{ day }}</b-form-checkbox>
                        </b-form-checkbox-group>
                    </b-form-group>
                    <b-form-group label="Preferred Shift Length">
                        <b-form-checkbox-group id="preferred_shift_length" v-model="form.preferred_shift_length">
                            <b-form-checkbox v-for="preferred_shift in shifts" :value="preferred_shift">{{ preferred_shift }}</b-form-checkbox>
                        </b-form-checkbox-group>
                    </b-form-group>
                </b-col>
                <b-col lg="6">
                    <b-form-group label="Preferred Times">
                        <b-form-checkbox-group id="preferred_times" v-model="form.preferred_times">
                            <b-form-checkbox v-for="time in times" :value="time">{{ time }}</b-form-checkbox>
                        </b-form-checkbox-group>
                    </b-form-group>
                    <b-form-group label="Work Weekends">
                        <b-form-checkbox v-model="form.work_weekends"
                                         :value="true"
                                         :unchecked-value="false">
                        </b-form-checkbox>
                    </b-form-group>
                    <b-form-group label="How many miles will you travel for an assignment?">
                        <b-form-checkbox-group id="travel_radius" v-model="form.travel_radius">
                            <b-form-checkbox v-for="distance in travelRadius" :value="distance">{{ distance }}</b-form-checkbox>
                        </b-form-checkbox-group>
                    </b-form-group>
                </b-col>
            </b-row>
            <!-- Driving History -->
            <b-row>
                <b-col>
                    <div class="h5">Driving History</div>
                    <em>{{ business.name }} will be reviewing your driving history. Untruthfulness will cause your application to be immediately rejected.</em>
                </b-col>
            </b-row>
            <hr>
            <b-row>
                <b-col lg="12">
                    <b-form-group label="Vehicle Year, Make, Model & Color">
                        <b-form-input v-model="form.vehicle"></b-form-input>
                    </b-form-group>
                </b-col>
            </b-row>
            <b-row>
                <b-col lg="3">
                    <b-form-group label="DUI within last 3 years?">
                        <b-form-checkbox v-model="form.dui"
                            :value="1"
                            :unchecked-value="0">
                        </b-form-checkbox>
                    </b-form-group>
                </b-col>
                <b-col lg="3">
                    <b-form-group label="Reckless Driving ticket within last 3 years?">
                        <b-form-checkbox v-model="form.reckless_driving"
                                         :value="1"
                                         :unchecked-value="0">
                        </b-form-checkbox>
                    </b-form-group>
                </b-col>
                <b-col lg="3">
                    <b-form-group label="Moving Violations within last 3 years?">
                        <b-form-checkbox v-model="form.moving_violation"
                                         :value="1"
                                         :unchecked-value="0">
                        </b-form-checkbox>
                    </b-form-group>
                    <b-form-group label="How many violations?" v-show="form.moving_violation">
                        <b-form-input v-model="form.moving_violation_count" type="number"></b-form-input>
                    </b-form-group>
                </b-col>
                <b-col lg="3">
                    <b-form-group label="Accidents in last 3 years?">
                        <b-form-checkbox v-model="form.accidents"
                                         :value="1"
                                         :unchecked-value="0">
                        </b-form-checkbox>
                    </b-form-group>
                    <b-form-group label="How many accidents?" v-show="form.accidents">
                        <b-form-input v-model="form.accident_count" type="number"></b-form-input>
                    </b-form-group>
                </b-col>
            </b-row>
            <b-row>
                <b-col lg="12">
                    <b-form-group label="If yes to any of the above, please explain: ">
                        <b-form-textarea id="driving_violations_desc"
                                         v-model="form.driving_violations_desc"
                                         :rows="3"
                                         :max-rows="6">
                        </b-form-textarea>
                    </b-form-group>
                </b-col>
            </b-row>
            <!-- Crimal History -->
            <b-row>
                <b-col>
                    <div class="h5">Crimnal History</div>
                    <em>{{ business.name }} will be reviewing your criminal history. Untruthfulness will cause your application to be immediately rejected.</em>
                </b-col>
            </b-row>
            <hr>
            <b-row>
                <b-col lg="3">
                    <b-form-group label="Felony Conviction(s)?">
                        <b-form-checkbox v-model="form.felony_conviction"
                                         :value="1"
                                         :unchecked-value="0">
                        </b-form-checkbox>
                    </b-form-group>
                </b-col>
                <b-col lg="3">
                    <b-form-group label="Theft related Conviction(s)?">
                        <b-form-checkbox v-model="form.theft_conviction"
                                         :value="1"
                                         :unchecked-value="0">
                        </b-form-checkbox>
                    </b-form-group>
                </b-col>
                <b-col lg="3">
                    <b-form-group label="Drug related Conviction(s)?">
                        <b-form-checkbox v-model="form.drug_conviction"
                                         :value="1"
                                         :unchecked-value="0">
                        </b-form-checkbox>
                    </b-form-group>
                </b-col>
                <b-col lg="3">
                    <b-form-group label="Violence related Conviction(s)?">
                        <b-form-checkbox v-model="form.violence_conviction"
                                         :value="1"
                                         :unchecked-value="0">
                        </b-form-checkbox>
                    </b-form-group>
                </b-col>
            </b-row>
            <b-row>
                <b-col lg="12">
                    <b-form-group label="If yes to any of the above, please explain: ">
                        <b-form-textarea id="criminal_history_desc"
                                         v-model="form.criminal_history_desc"
                                         :rows="3"
                                         :max-rows="6">
                        </b-form-textarea>
                    </b-form-group>
                </b-col>
            </b-row>
            <!-- Injury Status -->
            <b-row>
                <b-col>
                    <div class="h5">Injury Status</div>
                </b-col>
            </b-row>
            <hr>
            <b-row>
                <b-col lg="3">
                    <b-form-group label="Are you currently injured in any way that would interfere with duties?">
                        <b-form-checkbox v-model="form.currently_injured"
                                         :value="1"
                                         :unchecked-value="0">
                        </b-form-checkbox>
                    </b-form-group>
                </b-col>
                <b-col lg="3">
                    <b-form-group label="Have you ever been injured on the job?">
                        <b-form-checkbox v-model="form.previously_injured"
                                         :value="1"
                                         :unchecked-value="0">
                        </b-form-checkbox>
                    </b-form-group>
                </b-col>
                <b-col lg="3">
                    <b-form-group label="Can you stoop bend and lift up to 25lbs?">
                        <b-form-checkbox v-model="form.lift_25_lbs"
                                         :value="1"
                                         :unchecked-value="0">
                        </b-form-checkbox>
                    </b-form-group>
                </b-col>
                <b-col lg="3">
                    <b-form-group label="Have you ever received workman's compensation benefits?">
                        <b-form-checkbox v-model="form.workmans_comp"
                                         :value="1"
                                         :unchecked-value="0">
                        </b-form-checkbox>
                    </b-form-group>
                </b-col>
            </b-row>
            <b-row v-show="form.workmans_comp">
                <b-col lg="12">
                    <b-form-group label="Where did you receive workman's compensation benefits?">
                        <b-form-input v-model="form.workmans_comp_dates"></b-form-input>
                    </b-form-group>
                </b-col>
            </b-row>
            <b-row>
                <b-col lg="12">
                    <b-form-group label="If yes to any of the above, please explain: ">
                        <b-form-textarea id="injury_status_desc"
                                         v-model="form.injury_status_desc"
                                         :rows="3"
                                         :max-rows="6">
                        </b-form-textarea>
                    </b-form-group>
                </b-col>
            </b-row>
            <!-- Employment History -->
            <b-row>
                <b-col>
                    <div class="h5">Employment History</div>
                    <em>Please supply the requested information below for your previous 3 employers.
                        Inaccurate information (example: wrong phone number) will delay the processing of your application.
                        Please notify your work references that a {{ business.name }} representative will be contacting them.
                    </em>
                </b-col>
            </b-row>
            <hr>

            <b-row>
                <b-col lg="4">
                    <b-form-group label="Employer Name">
                        <b-form-input v-model="form.employer_1_name"></b-form-input>
                    </b-form-group>
                    <b-form-group label="Approximate Start Date">
                        <b-form-input
                                type="text"
                                id="approx_start_date_1"
                                class="datepicker"
                                v-model="form.employer_1_approx_start_date"
                        >
                        </b-form-input>
                    </b-form-group>
                    <b-form-group label="Job Title">
                        <b-form-input type="text" v-model="form.employer_1_job_title">
                        </b-form-input>
                    </b-form-group>
                </b-col>
                <b-col lg="4">
                    <b-form-group label="Employer City">
                        <b-form-input v-model="form.employer_1_city"></b-form-input>
                    </b-form-group>
                    <b-form-group label="Approximate End Date">
                        <b-form-input
                                type="text"
                                id="approx_end_date_1"
                                class="datepicker"
                                v-model="form.employer_1_approx_end_date"
                        >
                        </b-form-input>
                    </b-form-group>
                    <b-form-group label="Supervisor Name">
                        <b-form-input type="text" v-model="form.employer_1_supervisor_name">
                        </b-form-input>
                    </b-form-group>
                </b-col>
                <b-col lg="4">
                    <b-form-group label="Employer State">
                        <b-form-input v-model="form.employer_1_state"></b-form-input>
                    </b-form-group>
                    <b-form-group label="Employer Phone">
                        <b-form-input v-model="form.employer_1_phone"></b-form-input>
                    </b-form-group>
                    <b-form-group label="Reason for leaving?">
                        <b-form-input type="text" v-model="form.employer_1_reason_for_leaving">
                        </b-form-input>
                    </b-form-group>
                </b-col>
            </b-row>
            <b-row>
                <b-col lg="4">
                    <b-form-group label="Employer Name">
                        <b-form-input v-model="form.employer_2_name"></b-form-input>
                    </b-form-group>
                    <b-form-group label="Approximate Start Date">
                        <b-form-input
                                type="text"
                                id="approx_start_date_2"
                                class="datepicker"
                                v-model="form.employer_2_approx_start_date"
                        >
                        </b-form-input>
                    </b-form-group>
                    <b-form-group label="Job Title">
                        <b-form-input type="text" v-model="form.employer_2_job_title">
                        </b-form-input>
                    </b-form-group>
                </b-col>
                <b-col lg="4">
                    <b-form-group label="Employer City">
                        <b-form-input v-model="form.employer_2_city"></b-form-input>
                    </b-form-group>
                    <b-form-group label="Approximate End Date">
                        <b-form-input
                                type="text"
                                id="approx_end_date_2"
                                class="datepicker"
                                v-model="form.employer_2_approx_end_date"
                        >
                        </b-form-input>
                    </b-form-group>
                    <b-form-group label="Supervisor Name">
                        <b-form-input type="text" v-model="form.employer_2_supervisor_name">
                        </b-form-input>
                    </b-form-group>
                </b-col>
                <b-col lg="4">
                    <b-form-group label="Employer State">
                        <b-form-input v-model="form.employer_2_state"></b-form-input>
                    </b-form-group>
                    <b-form-group label="Employer Phone">
                        <b-form-input v-model="form.employer_2_phone"></b-form-input>
                    </b-form-group>
                    <b-form-group label="Reason for leaving?">
                        <b-form-input type="text" v-model="form.employer_2_reason_for_leaving">
                        </b-form-input>
                    </b-form-group>
                </b-col>
            </b-row>
            <b-row>
                <b-col lg="4">
                    <b-form-group label="Employer Name">
                        <b-form-input v-model="form.employer_3_name"></b-form-input>
                    </b-form-group>
                    <b-form-group label="Approximate Start Date">
                        <b-form-input
                                type="text"
                                id="approx_start_date_3"
                                class="datepicker"
                                v-model="form.employer_3_approx_start_date"
                        >
                        </b-form-input>
                    </b-form-group>
                    <b-form-group label="Job Title">
                        <b-form-input type="text" v-model="form.employer_3_job_title">
                        </b-form-input>
                    </b-form-group>
                </b-col>
                <b-col lg="4">
                    <b-form-group label="Employer City">
                        <b-form-input v-model="form.employer_3_city"></b-form-input>
                    </b-form-group>
                    <b-form-group label="Approximate End Date">
                        <b-form-input
                                type="text"
                                id="approx_end_date_3"
                                class="datepicker"
                                v-model="form.employer_3_approx_end_date"
                        >
                        </b-form-input>
                    </b-form-group>
                    <b-form-group label="Supervisor Name">
                        <b-form-input type="text" v-model="form.employer_3_supervisor_name">
                        </b-form-input>
                    </b-form-group>
                </b-col>
                <b-col lg="4">
                    <b-form-group label="Employer State">
                        <b-form-input v-model="form.employer_3_state"></b-form-input>
                    </b-form-group>
                    <b-form-group label="Employer Phone">
                        <b-form-input v-model="form.employer_3_phone"></b-form-input>
                    </b-form-group>
                    <b-form-group label="Reason for leaving?">
                        <b-form-input type="text" v-model="form.employer_3_reason_for_leaving">
                        </b-form-input>
                    </b-form-group>
                </b-col>
            </b-row>
            <hr>
            <!-- Personal References -->
            <b-row>
                <b-col>
                    <div class="h5">Personal References</div>
                    <em>Please list three references that have personal knowledge of you and your character.
                        Do not use anyone used in the Professional Reference section.
                        Inaccurate information (example: wrong phone number) will delay the processing of your application.
                        Please notify your personal references that a {{ business.name }} representative will be contacting them.
                    </em>
                </b-col>
            </b-row>
            <hr>
            <b-row>
                <b-col lg="4">
                    <b-form-group label="Reference Name">
                        <b-form-input v-model="form.reference_1_name"></b-form-input>
                    </b-form-group>
                </b-col>
                <b-col lg="4">
                    <b-form-group label="Reference Phone">
                        <b-form-input v-model="form.reference_1_phone"></b-form-input>
                    </b-form-group>
                </b-col>
                <b-col lg="4">
                    <b-form-group label="Reference Relationship">
                        <b-form-input v-model="form.reference_1_relationship"></b-form-input>
                    </b-form-group>
                </b-col>
            </b-row>
            <hr>
            <b-row>
                <b-col lg="4">
                    <b-form-group label="Reference Name">
                        <b-form-input v-model="form.reference_2_name"></b-form-input>
                    </b-form-group>
                </b-col>
                <b-col lg="4">
                    <b-form-group label="Reference Phone">
                        <b-form-input v-model="form.reference_2_phone"></b-form-input>
                    </b-form-group>
                </b-col>
                <b-col lg="4">
                    <b-form-group label="Reference Relationship">
                        <b-form-input v-model="form.reference_2_relationship"></b-form-input>
                    </b-form-group>
                </b-col>
            </b-row>
            <hr>
            <b-row>
                <b-col lg="4">
                    <b-form-group label="Reference Name">
                        <b-form-input v-model="form.reference_3_name"></b-form-input>
                    </b-form-group>
                </b-col>
                <b-col lg="4">
                    <b-form-group label="Reference Phone">
                        <b-form-input v-model="form.reference_3_phone"></b-form-input>
                    </b-form-group>
                </b-col>
                <b-col lg="4">
                    <b-form-group label="Reference Relationship">
                        <b-form-input v-model="form.reference_3_relationship"></b-form-input>
                    </b-form-group>
                </b-col>
            </b-row>
            <!-- How did you hear about us? -->
            <b-row>
                <b-col>
                    <div class="h5">How did you hear about us?</div>
                    <b-form-checkbox-group id="heard_about" v-model="form.heard_about">
                        <b-form-checkbox v-for="about in heardAbout" :value="about">{{ about }}</b-form-checkbox>
                    </b-form-checkbox-group>
                </b-col>
            </b-row>
            <hr>
            <b-row>
                <b-col>
                    <div class="btn btn-success" @click="saveApp">Update</div>
                </b-col>
            </b-row>
        </b-card>
    </b-container>
</template>

<style lang="scss">
</style>

<script>
    export default {
        props: ['application', 'business', 'positions'],

        data() {
            return{
                days: ['Mon', 'Tues', 'Wed', 'Thurs', 'Fri'],
                times: ['Mornings', 'Afternoons', 'Evenings', 'Nights'],
                shifts: [1, 4, 8, 12],
                travelRadius: [5, 10, 15, 20],
                heardAbout: ['Friend', 'Online Ad', 'TV', 'GN Website', 'Job Fair', 'Other'],
                form: new Form(this.application)
            }
        },

        created() {

        },

        mounted() {
            let dob = jQuery('#date_of_birth');
            let preferredStartDate = jQuery('#preferred_start_date');
            let component = this;

            dob.datepicker({
                forceParse: false,
                autoclose: true,
                todayHighlight: true
            }).on("changeDate", function () {
                component.form.date_of_birth = dob.val();
            });

            preferredStartDate.datepicker({
                autoclose: true
            }).on("changeDate", function () {
                component.form.preferred_start_date = preferredStartDate.val();
            });


            let employStartDate1 = jQuery('#approx_start_date_1');
            let employEndDate1 = jQuery('#approx_end_date_1');

            employStartDate1.datepicker({
                autoclose: true
            }).on("changeDate", function () {
                component.form.employer_1_approx_start_date = employStartDate1.val();
            });

            employEndDate1.datepicker({
                autoclose: true
            }).on("changeDate", function () {
                component.form.employer_1_approx_end_date = employEndDate1.val();
            });

            let employStartDate2 = jQuery('#approx_start_date_2');
            let employEndDate2 = jQuery('#approx_end_date_2');

            employStartDate2.datepicker({
                autoclose: true
            }).on("changeDate", function () {
                component.form.employer_2_approx_start_date = employStartDate2.val();
            });

            employEndDate2.datepicker({
                autoclose: true
            }).on("changeDate", function () {
                component.form.employer_2_approx_end_date = employEndDate2.val();
            });

            let employStartDate3 = jQuery('#approx_start_date_3');
            let employEndDate3 = jQuery('#approx_end_date_3');

            employStartDate3.datepicker({
                autoclose: true
            }).on("changeDate", function () {
                component.form.employer_3_approx_start_date = employStartDate3.val();
            });

            employEndDate3.datepicker({
                autoclose: true
            }).on("changeDate", function () {
                component.form.employer_3_approx_end_date = employEndDate3.val();
            });

        },

        methods: {
            saveApp() {
                this.form.put('/business/caregivers/applications/'+this.application.id);
            }
        },

        computed: {

        }
    }
</script>