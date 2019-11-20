<template>
    <b-container>
        <b-card title="Caregiver Application">
            <b-row>

                <b-col lg="4">
                    <b-form-group label="First Name*">
                        <b-form-input v-model="form.first_name" autofocus></b-form-input>
                        <input-help :form="form" field="first_name" text=""></input-help>
                    </b-form-group>
                </b-col>

                <b-col lg="4">
                    <b-form-group label="Middle Initial">
                        <b-form-input v-model="form.middle_initial"></b-form-input>
                        <input-help :form="form" field="middle_initial" text=""></input-help>
                    </b-form-group>
                </b-col>

                <b-col lg="4">
                    <b-form-group label="Last Name*">
                        <b-form-input v-model="form.last_name"></b-form-input>
                        <input-help :form="form" field="last_name" text=""></input-help>
                    </b-form-group>
                </b-col>

            </b-row>
            <b-row>

                <b-col lg="4">
                    <b-form-group label="Date of Birth">
                        <date-picker v-model="form.date_of_birth" />
                        <input-help :form="form" field="date_of_birth" text=""></input-help>
                    </b-form-group>
                </b-col>

                <b-col lg="4">
                    <b-form-group label="Social Security Number">
                        <mask-input type="ssn" v-model="form.ssn"></mask-input>
                        <input-help :form="form" field="ssn" text=""></input-help>
                    </b-form-group>
                </b-col>

                <b-col lg="4">
                    <b-form-group label="Email*">
                        <b-form-input v-model="form.email"></b-form-input>
                        <input-help :form="form" field="email" text=""></input-help>
                    </b-form-group>
                </b-col>

            </b-row>

            <b-row>
                <b-col lg="6">
                    <b-form-group label="Address*">
                        <b-form-input v-model="form.address"></b-form-input>
                        <input-help :form="form" field="address" text=""></input-help>
                    </b-form-group>
                </b-col>

                <b-col lg="6">
                    <b-form-group label="Address 2">
                        <b-form-input v-model="form.address_2"></b-form-input>
                        <input-help :form="form" field="address_2" text=""></input-help>
                    </b-form-group>
                </b-col>
            </b-row>

            <b-row>
                <b-col lg="4">
                    <b-form-group label="City*">
                        <b-form-input v-model="form.city"></b-form-input>
                        <input-help :form="form" field="city" text=""></input-help>
                    </b-form-group>
                </b-col>

                <b-col lg="4">
                    <b-form-group label="State*" label-for="state">
                        <b-form-select name="state" :options="states.getOptions()" v-model="form.state" />
                        <input-help :form="form" field="state" text=""></input-help>
                    </b-form-group>
                </b-col>

                <b-col lg="4">
                    <b-form-group label="Zip*">
                        <b-form-input v-model="form.zip"></b-form-input>
                        <input-help :form="form" field="zip" text=""></input-help>
                    </b-form-group>
                </b-col>

            </b-row>
            <b-row>

                <b-col lg="4">
                    <b-form-group label="Home Phone">
                        <mask-input v-model="form.home_phone" name="home_phone"></mask-input>
                        <input-help :form="form" field="home_phone" text=""></input-help>
                    </b-form-group>
                </b-col>

            </b-row>

            <b-row>
                <b-col lg="8">
                    <b-form-group label="Emergency Contact Name">
                        <b-form-input v-model="form.emergency_contact_name"></b-form-input>
                        <input-help :form="form" field="emergency_contact_name" text=""></input-help>
                    </b-form-group>
                </b-col>
                <b-col lg="4">
                    <b-form-group label="Emergency Contact Phone">
                        <b-form-input v-model="form.emergency_contact_phone"></b-form-input>
                        <input-help :form="form" field="emergency_contact_phone" text=""></input-help>
                    </b-form-group>
                </b-col>
            </b-row>

            <b-row>
                <b-col lg="6">
                    <b-form-group :label="`Have you worked with ${business.name} before?`">
                        <b-form-radio-group v-model="form.worked_here_before">
                            <b-radio :value="true">Yes</b-radio>
                            <b-radio :value="false">No</b-radio>
                            <input-help :form="form" field="worked_here_before" text=""></input-help>
                        </b-form-radio-group>
                    </b-form-group>
                </b-col>
                <b-col lg="6">
                    <b-form-group label="Which Location?" v-show="form.worked_here_before">
                        <b-form-input v-model="form.worked_before_location"></b-form-input>
                        <input-help :form="form" field="worked_before_location" text=""></input-help>
                    </b-form-group>
                </b-col>
            </b-row>
            <hr>
            <b-row>
                <b-col lg="12">
                    <b-form-group label="Which position are you applying for?">
                        <b-form-input v-model="form.position"></b-form-input>
                        <input-help :form="form" field="position" text=""></input-help>
                    </b-form-group>
                </b-col>
            </b-row>
            <b-row>
                <b-col lg="12">
                    <b-form-group  label="Level of Credentialing:">
                        <b-form-radio-group v-model="form.certification">
                            <b-radio value="Companion">Companion</b-radio>
                            <b-radio value="HHA">HHA</b-radio>
                            <b-radio value="CNA">CNA</b-radio>
                            <b-radio value="LPN">LPN</b-radio>
                            <b-radio value="RN">RN</b-radio>
                            <input-help :form="form" field="certification" text=""></input-help>
                        </b-form-radio-group>
                    </b-form-group>
                </b-col>
            </b-row>
            <b-row>
                <b-col lg="12">
                    <b-form-group label="License Number (if applicable):">
                        <b-form-input v-model="form.license_number"></b-form-input>
                        <input-help :form="form" field="license_number" text=""></input-help>
                    </b-form-group>
                </b-col>
            </b-row>
            <b-row>
                <b-col lg="12">
                    <b-form-group label="Healthcare Training School Name:">
                        <b-form-input v-model="form.training_school"></b-form-input>
                        <input-help :form="form" field="training_school" text=""></input-help>
                    </b-form-group>
                </b-col>
            </b-row>
            <!-- Availability -->
            <b-row>
                <b-col>
                    <div class="h5">Availability</div>
                    <em>Note: Home Care is a 24x7 business.  It is normal to be expected to work some weekends and Holidays.</em>
                </b-col>
            </b-row>
            <hr>
            <b-row>
                <b-col lg="6">
                    <b-form-group label="Preferred Start Date">
                        <date-picker v-model="form.preferred_start_date" />
                        <input-help :form="form" field="preferred_start_date" text=""></input-help>
                    </b-form-group>
                    <b-form-group label="Preferred Days">
                        <b-form-checkbox-group id="preferred_days" v-model="form.preferred_days">
                            <b-form-checkbox v-for="day in days" :value="day" :key="day">{{ day }}</b-form-checkbox>
                        </b-form-checkbox-group>
                    </b-form-group>
                    <b-form-group label="Preferred Shift Length (Hours)">
                        <b-form-checkbox-group id="preferred_shift_length" v-model="form.preferred_shift_length">
                            <b-form-checkbox v-for="preferred_shift in shifts" :value="preferred_shift" :key="preferred_shift.id">{{ preferred_shift }}</b-form-checkbox>
                        </b-form-checkbox-group>
                    </b-form-group>
                </b-col>
                <b-col lg="6">
                    <b-form-group label="Preferred Times">
                        <b-form-checkbox-group id="preferred_times" v-model="form.preferred_times">
                            <b-form-checkbox v-for="time in times" :value="time" :key="time">{{ time }}</b-form-checkbox>
                        </b-form-checkbox-group>
                    </b-form-group>
                    <b-form-group label="How many miles will you travel for an assignment?">
                        <b-form-checkbox-group id="travel_radius" v-model="form.travel_radius">
                            <b-form-checkbox v-for="distance in travelRadius" :value="distance" :key="distance">{{ distance }}</b-form-checkbox>
                        </b-form-checkbox-group>
                    </b-form-group>
                </b-col>
            </b-row>
            <!-- Driving History -->
            <b-row>
                <b-col>
                    <div class="h5">Driving History</div>
                    <em>As part of the background screening process, a check of your driving record may be request by a family seeking your services.  Failure to disclose tickets or an accident will cause your application to be immediately rejected.</em>
                </b-col>
            </b-row>
            <hr>
            <b-row>
                <b-col lg="12">
                    <b-form-group label="Vehicle Year, Make, Model & Color">
                        <b-form-input v-model="form.vehicle"></b-form-input>
                        <input-help :form="form" field="vehicle" text=""></input-help>
                    </b-form-group>
                </b-col>
            </b-row>
            <b-row>
                <b-col lg="3">
                    <b-form-group label="DUI within last 3 years?">
                        <b-form-checkbox v-model="form.dui"
                                         :value="true"
                                         :unchecked-value="false">
                        </b-form-checkbox>
                    </b-form-group>
                </b-col>
                <b-col lg="3">
                    <b-form-group label="Reckless Driving ticket within last 3 years?">
                        <b-form-checkbox v-model="form.reckless_driving"
                                         :value="true"
                                         :unchecked-value="false">
                        </b-form-checkbox>
                    </b-form-group>
                </b-col>
                <b-col lg="3">
                    <b-form-group label="Moving Violations within last 3 years?">
                        <b-form-checkbox v-model="form.moving_violation"
                                         :value="true"
                                         :unchecked-value="false">
                        </b-form-checkbox>
                    </b-form-group>
                    <b-form-group label="How many violations?" v-show="form.moving_violation">
                        <b-form-input v-model="form.moving_violation_count" type="number"></b-form-input>
                        <input-help :form="form" field="moving_violation_count" text=""></input-help>
                    </b-form-group>
                </b-col>
                <b-col lg="3">
                    <b-form-group label="Accidents in last 3 years?">
                        <b-form-checkbox v-model="form.accidents"
                                         :value="true"
                                         :unchecked-value="false">
                        </b-form-checkbox>
                    </b-form-group>
                    <b-form-group label="How many accidents?" v-show="form.accidents">
                        <b-form-input v-model="form.accident_count" type="number"></b-form-input>
                        <input-help :form="form" field="accident_count" text=""></input-help>
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
                        <input-help :form="form" field="driving_violations_desc" text=""></input-help>
                    </b-form-group>
                </b-col>
            </b-row>
            <!-- Crimal History -->
            <b-row>
                <b-col>
                    <div class="h5">Criminal History</div>
                    <em>As part of the background screening process, a criminal background check will be run on all referral candidates.  Failure to disclose an arrest or conviction will cause your application to be rejected.</em>
                </b-col>
            </b-row>
            <hr>
            <b-row>
                <b-col lg="3">
                    <b-form-group label="Felony Conviction(s)?">
                        <b-form-checkbox v-model="form.felony_conviction"
                                         :value="true"
                                         :unchecked-value="false">
                        </b-form-checkbox>
                    </b-form-group>
                </b-col>
                <b-col lg="3">
                    <b-form-group label="Theft related Conviction(s)?">
                        <b-form-checkbox v-model="form.theft_conviction"
                                         :value="true"
                                         :unchecked-value="false">
                        </b-form-checkbox>
                    </b-form-group>
                </b-col>
                <b-col lg="3">
                    <b-form-group label="Drug related Conviction(s)?">
                        <b-form-checkbox v-model="form.drug_conviction"
                                         :value="true"
                                         :unchecked-value="false">
                        </b-form-checkbox>
                    </b-form-group>
                </b-col>
                <b-col lg="3">
                    <b-form-group label="Violence related Conviction(s)?">
                        <b-form-checkbox v-model="form.violence_conviction"
                                         :value="true"
                                         :unchecked-value="false">
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
                        <input-help :form="form" field="criminal_history_desc" text=""></input-help>
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
                                         :value="true"
                                         :unchecked-value="false">
                        </b-form-checkbox>
                    </b-form-group>
                </b-col>
                <b-col lg="3">
                    <b-form-group label="Have you ever been injured on the job?">
                        <b-form-checkbox v-model="form.previously_injured"
                                         :value="true"
                                         :unchecked-value="false">
                        </b-form-checkbox>
                    </b-form-group>
                </b-col>
                <b-col lg="3">
                    <b-form-group label="Can you stoop bend and lift up to 25lbs?">
                        <b-form-checkbox v-model="form.lift_25_lbs"
                                         :value="true"
                                         :unchecked-value="false">
                        </b-form-checkbox>
                    </b-form-group>
                </b-col>
                <b-col lg="3">
                    <b-form-group label="Have you ever received workman's compensation benefits?">
                        <b-form-checkbox v-model="form.workmans_comp"
                                         :value="true"
                                         :unchecked-value="false">
                        </b-form-checkbox>
                    </b-form-group>
                </b-col>
            </b-row>
            <b-row v-show="form.workmans_comp">
                <b-col lg="12">
                    <b-form-group label="Where did you receive workman's compensation benefits?">
                        <b-form-input v-model="form.workmans_comp_dates"></b-form-input>
                        <input-help :form="form" field="workmans_comp_dates" text=""></input-help>
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
                        <input-help :form="form" field="injury_status_desc" text=""></input-help>
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
            <template v-for="i in 3">
                <b-row :key="i">
                    <b-col lg="4">
                        <b-form-group label="Employer Name">
                            <b-form-input v-model="form[`employer_${i}_name`]"></b-form-input>
                            <input-help :form="form" :field="`employer_${i}_name`" text=""></input-help>
                        </b-form-group>
                    </b-col>

                    <b-col lg="4">
                        <b-form-group label="Approximate Start Date">
                            <date-picker v-model="form[`employer_${i}_approx_start_date`]" />
                            <input-help :form="form" :field="`employer_${i}_approx_start_date`" text=""></input-help>
                        </b-form-group>
                    </b-col>

                    <b-col lg="4">
                        <b-form-group label="Approximate End Date">
                            <date-picker v-model="form[`employer_${i}_approx_end_date`]" />
                            <input-help :form="form" :field="`employer_${i}_approx_end_date`" text=""></input-help>
                        </b-form-group>
                    </b-col>

                    <b-col lg="4">
                        <b-form-group label="Employer City">
                            <b-form-input v-model="form[`employer_${i}_city`]"></b-form-input>
                            <input-help :form="form" :field="`employer_${i}_city`" text=""></input-help>
                        </b-form-group>
                    </b-col>

                    <b-col lg="4">
                        <b-form-group label="Employer State">
                            <b-form-select :options="states.getOptions()" v-model="form[`employer_${i}_state`]" />
                            <input-help :form="form" :field="'employer_'+i+'_state'" text=""></input-help>
                        </b-form-group>
                    </b-col>

                    <b-col lg="4">
                        <b-form-group label="Employer Phone">
                            <mask-input v-model="form[`employer_${i}_phone`]"></mask-input>
                            <input-help :form="form" :field="`employer_${i}_phone`" text=""></input-help>
                        </b-form-group>
                    </b-col>

                    <b-col lg="4">
                        <b-form-group label="Job Title">
                            <b-form-input type="text"
                                          v-model="form[`employer_${i}_job_title`]">
                            </b-form-input>
                            <input-help :form="form" :field="`employer_${i}_job_title`" text=""></input-help>
                        </b-form-group>
                    </b-col>

                    <b-col lg="4">
                        <b-form-group label="Supervisor Name">
                            <b-form-input type="text"
                                          v-model="form[`employer_${i}_supervisor_name`]">
                            </b-form-input>
                            <input-help :form="form" :field="`employer_${i}_supervisor_name`" text=""></input-help>
                        </b-form-group>
                    </b-col>

                    <b-col lg="4">
                        <b-form-group label="Reason for leaving?">
                            <b-form-input type="text"
                                          v-model="form[`employer_${i}_reason_for_leaving`]">
                            </b-form-input>
                            <input-help :form="form" :field="`employer_${i}_reason_for_leaving`" text=""></input-help>
                        </b-form-group>
                    </b-col>

                </b-row>
                <hr v-if="i != 3">
            </template>
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
            <template v-for="i in 3">
                <b-row :key="i">
                    <b-col lg="4">
                        <b-form-group label="Reference Name">
                            <b-form-input v-model="form[`reference_${i}_name`]"></b-form-input>
                            <input-help :form="form" :field="`reference_${i}_name`" text=""></input-help>
                        </b-form-group>
                    </b-col>
                    <b-col lg="4">
                        <b-form-group label="Reference Phone">
                            <mask-input v-model="form[`reference_${i}_phone`]"></mask-input>
                            <input-help :form="form" :field="`reference_${i}_phone`" text=""></input-help>
                        </b-form-group>
                    </b-col>
                    <b-col lg="4">
                        <b-form-group label="Reference Relationship">
                            <b-form-input v-model="form[`reference_${i}_relationship`]"></b-form-input>
                            <input-help :form="form" :field="`reference_${i}_relationship`" text=""></input-help>
                        </b-form-group>
                    </b-col>
                </b-row>
                <hr :key="i" v-if="i != 3">
            </template>
            <!-- Technology -->
            <b-row>
                <b-col>
                    <div class="h5">Technology</div>
                    <em>{{ business.name }} will use telephony, emails, texts and phone calls to offer you referrals and to communicate and verify home referrals and visits for it's clients.  Your ability to communicate via phone, text message and email is necessary to be referred by {{ business.name }}.</em>
                </b-col>
            </b-row>
            <hr>
            <b-row>
                <b-col lg="6">
                    <b-form-group label="Do you have a cell phone?">
                        <b-form-radio-group v-model="form.has_cell_phone">
                            <b-radio :value="true">Yes</b-radio>
                            <b-radio :value="false">No</b-radio>
                            <input-help :form="form" field="has_cell_phone" text=""></input-help>
                        </b-form-radio-group>
                    </b-form-group>
                </b-col>
            </b-row>
            <b-row v-show="form.has_cell_phone">
                <b-col lg="6">
                    <b-form-group label="Cell Phone Number">
                        <mask-input v-model="form.cell_phone" name="cell_phone"></mask-input>
                        <input-help :form="form" field="cell_phone" text=""></input-help>
                    </b-form-group>
                </b-col>
                <b-col lg="6">
                    <b-form-group label="Cell Phone Provider">
                        <b-form-input v-model="form.cell_phone_provider"></b-form-input>
                        <input-help :form="form" field="cell_phone_provider" text=""></input-help>
                    </b-form-group>
                </b-col>
                <b-col lg="6">
                    <b-form-group label="Is it a smart phone? (Receives text messages and email)">
                        <b-form-radio-group v-model="form.has_smart_phone">
                            <b-radio :value="true">Yes</b-radio>
                            <b-radio :value="false">No</b-radio>
                            <input-help :form="form" field="has_smart_phone" text=""></input-help>
                        </b-form-radio-group>
                    </b-form-group>
                </b-col>
                <b-col lg="6">
                    <b-form-group label="Do you email and text on a regular basis?">
                        <b-form-radio-group v-model="form.can_text">
                            <b-radio :value="true">Yes</b-radio>
                            <b-radio :value="false">No</b-radio>
                            <input-help :form="form" field="can_text" text=""></input-help>
                        </b-form-radio-group>
                    </b-form-group>
                </b-col>
            </b-row>
            <!-- How did you hear about us? -->
            <b-row>
                <b-col>
                    <div class="h5">How did you hear about us?</div>
                    <b-form-checkbox-group id="heard_about" v-model="form.heard_about">
                        <b-form-checkbox v-for="about in heardAbout" :value="about" :key="about">{{ about }}</b-form-checkbox>
                    </b-form-checkbox-group>
                </b-col>
            </b-row>
            <hr>
            <b-row>
                <b-col sm="6" v-if="signature">
                    <strong>Caregiver Signature</strong>
                    <div v-html="signature.content" class="signature"></div>
                </b-col>
                <b-col v-else class="d-flex mb-2 flex-wrap align-content-stretch">
                    <signature-pad
                            class="mr-2 my-1"
                            v-model="signature"
                            :buttonTitle=" 'Add Caregiver Signature' ">
                    </signature-pad>
                </b-col>
            </b-row>
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
    import States from '../../classes/States';

    export default {
        props: ['application', 'business'],

        data() {
            return{
                signature: {},
                days: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                times: ['Mornings', 'Afternoons', 'Evenings', 'Nights'],
                shifts: [1, 4, 8, 12],
                travelRadius: [5, 10, 15, 20, 30, 40],
                heardAbout: ['Friend', 'Online Ad', 'TV', 'GN Website', 'Job Fair', 'Other'],
                states: new States(),
                form: new Form({}),
            }
        },

        created() {
            this.makeForm(this.application);
        },

        methods: {
            makeForm(application) {
                let dateFields = [
                    'date_of_birth',
                    'preferred_start_date',
                    'employer_1_approx_start_date',
                    'employer_1_approx_end_date',
                    'employer_2_approx_start_date',
                    'employer_2_approx_end_date',
                    'employer_3_approx_start_date',
                    'employer_3_approx_end_date'
                ];

                let data = { ... application };

                for (let field of dateFields) {
                    if (data[field]) {
                        data[field] = moment(data[field], 'YYYY-MM-DD').format('MM/DD/YYYY');
                    }
                }

                this.signature = data.caregiver_signature;
                delete data.caregiver_signature;
                this.form = new Form(data);

            },

            saveApp() {
                this.form.put('/business/caregivers/applications/'+this.application.id);
            }
        },

        computed: {

        }
    }
</script>