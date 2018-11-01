<template>
    <form @submit.prevent="submitStepOne()" @keydown="form.clearError($event.target.name)">
        <!-- Client Personal Data -->
        <b-card border-variant="secondary" header="Client Personal Data">
            <b-row>
                <b-col lg="4">
                    <b-form-group label="First Name" label-for="firstname">
                        <b-form-input
                            id="firstname"
                            name="firstname"
                            type="text"
                            v-model="form.firstname"
                            required
                        >
                        </b-form-input>
                        <input-help :form="form" field="firstname" text="Enter their first name."></input-help>
                    </b-form-group>
                </b-col>
                <b-col lg="4">
                    <b-form-group label="Middle Initial">
                        <b-form-input v-model="form.middle_initial"></b-form-input>
                    </b-form-group>
                </b-col>
                <b-col lg="4">
                    <b-form-group label="Last Name" label-for="lastname">
                        <b-form-input
                            id="lastname"
                            name="lastname"
                            type="text"
                            v-model="form.lastname"
                            required
                        >
                        </b-form-input>
                        <input-help :form="form" field="lastname" text="Enter their last name."></input-help>
                    </b-form-group>
                </b-col>
            </b-row>
            <b-row>
                <b-col lg="6">
                    <b-form-group label="Email Address" label-for="email">
                        <b-row>
                            <b-col>
                                <b-form-input
                                    id="email"
                                    name="email"
                                    type="email"
                                    v-model="form.email"
                                >
                                </b-form-input>
                            </b-col>
                        </b-row>
                    </b-form-group>
                </b-col>
                <b-col lg="6">
                    <b-form-group label="Phone Number">
                        <b-form-input id="phone_number" v-model="form.phone_number"></b-form-input>
                    </b-form-group>
                </b-col>
            </b-row>

            <b-row>
                <b-col lg="6">
                    <b-form-group label="Date of Birth" label-for="date_of_birth">
                        <mask-input v-model="form.date_of_birth" id="date_of_birth" type="date"></mask-input>
                        <input-help :form="form" field="date_of_birth"
                                    text="Enter their date of birth. Ex: MM/DD/YYYY"></input-help>
                    </b-form-group>
                </b-col>
                <b-col lg="6">
                    <b-form-group label="Gender">
                        <b-form-radio-group id="gender" v-model="form.gender" horizontal>
                            <b-form-radio value="M">Male</b-form-radio>
                            <b-form-radio value="F">Female</b-form-radio>
                        </b-form-radio-group>
                    </b-form-group>
                </b-col>
            </b-row>

            <b-row>
                <b-col>
                    <b-form-group label="Address">
                        <b-form-input v-model="form.address"></b-form-input>
                    </b-form-group>
                </b-col>
            </b-row>

            <b-row>
                <b-col>
                    <b-form-group label="Does the client live in a facility or subdivision?" horizontal :label-cols="8"
                                  breakpoint="md">
                        <b-form-radio-group v-model="form.facility">
                            <b-form-radio :value="true">Yes</b-form-radio>
                            <b-form-radio :value="false">No</b-form-radio>
                        </b-form-radio-group>
                    </b-form-group>
                    <b-form-group v-show="form.facility"
                                  label="Please provide name, gate code, parking instructions, etc.">
                        <b-form-textarea v-model="form.facility_instructions"></b-form-textarea>
                    </b-form-group>
                </b-col>
            </b-row>
        </b-card>

        <!-- General Information -->
        <b-card border-variant="secondary" header="General Information">
            <b-row>
                <b-col>
                    <b-form-group label="Primary Medical Condition(s)">
                        <b-form-textarea
                            v-model="form.primary_conditions">
                        </b-form-textarea>
                    </b-form-group>
                    <b-form-group label="Reason(s) for Service">
                        <b-form-textarea
                            v-model="form.service_reasons">
                        </b-form-textarea>
                    </b-form-group>
                    <b-form-group label="Goal(s) of Service">
                        <b-form-textarea
                            v-model="form.service_goals">
                        </b-form-textarea>
                    </b-form-group>
                    <b-form-group label="Allergies (Food/Medication)">
                        <b-form-textarea
                            v-model="form.allergies">
                        </b-form-textarea>
                    </b-form-group>
                    <b-form-group label="Medical Equipment in the Home (ex: wheelchair, walker, oxygen)">
                        <b-form-textarea
                            v-model="form.medical_equipment">
                        </b-form-textarea>
                    </b-form-group>
                </b-col>
            </b-row>

            <b-row>
                <b-col lg="6">
                    <b-form-group label="Client Height">
                        <b-form-input v-model="form.height"></b-form-input>
                    </b-form-group>
                </b-col>
                <b-col lg="6">
                    <b-form-group label="Client Weight">
                        <b-form-input v-model="form.weight"></b-form-input>
                    </b-form-group>
                </b-col>
            </b-row>
        </b-card>

        <!-- Hands-On Care Activities -->
        <b-card border-variant="secondary" header="Hands-On Care Activities Requested">
            <div v-for="item in activities.hands_on" :key="item.id">
                <b-form-group :label="item.name" horizontal :label-cols="4" breakpoint="md">
                    <b-form-radio-group v-model="form.activities[item.id]">
                        <b-form-radio value="none">No Assist</b-form-radio>
                        <b-form-radio value="partial">Partial Assist</b-form-radio>
                        <b-form-radio value="full">Total Assist</b-form-radio>
                    </b-form-radio-group>
                </b-form-group>
            </div>
        </b-card>

        <!-- Household Activities -->
        <b-card border-variant="secondary" header="Household Activities Requested">
            <div v-for="item in activities.household" :key="item.id">
                <b-form-group :label="item.name" horizontal :label-cols="4" breakpoint="md">
                    <b-form-radio-group v-model="form.activities[item.id]">
                        <b-form-radio value="none">No Assist</b-form-radio>
                        <b-form-radio value="partial">Partial Assist</b-form-radio>
                        <b-form-radio value="full">Total Assist</b-form-radio>
                    </b-form-radio-group>
                </b-form-group>
            </div>
        </b-card>

        <!-- Primary Care Physician & Pharmacy -->
        <b-row>
            <b-col lg="6">
                <b-card border-variant="secondary" header="Primary Care Physician">
                    <b-form-group label="Name" class="mt-2">
                        <b-form-input v-model="form.physician_name"></b-form-input>
                    </b-form-group>
                    <b-form-group label="Phone">
                        <b-form-input v-model="form.physician_phone"></b-form-input>
                    </b-form-group>
                    <b-form-group label="Address">
                        <b-form-input v-model="form.physician_address"></b-form-input>
                    </b-form-group>
                </b-card>
            </b-col>
            <b-col lg="6">
                <b-card border-variant="secondary" header="Pharmacy">
                    <b-form-group label="Name" class="mt-2">
                        <b-form-input v-model="form.pharmacy_name"></b-form-input>
                    </b-form-group>
                    <b-form-group label="Phone">
                        <b-form-input v-model="form.pharmacy_phone"></b-form-input>
                    </b-form-group>
                    <b-form-group label="Address">
                        <b-form-input v-model="form.pharmacy_address"></b-form-input>
                    </b-form-group>
                </b-card>
            </b-col>
        </b-row>

        <!-- todo Prescription Medication List here -->

        <!-- Hospice Information -->
        <b-card border-variant="secondary" header="Hospice Information">
            <b-form-group label="Is the client under Hospice Care?" horizontal :label-cols="8" breakpoint="md">
                <b-form-radio-group v-model="form.hospice_care">
                    <b-form-radio :value="true">Yes</b-form-radio>
                    <b-form-radio :value="false">No</b-form-radio>
                </b-form-radio-group>
            </b-form-group>
            <template v-if="form.hospice_care">
                <b-form-group label="Office Location (city)">
                    <b-form-input v-model="form.hospice_office_location"></b-form-input>
                </b-form-group>
                <b-form-group label="Case Manager Name">
                    <b-form-input v-model="form.hospice_case_manager"></b-form-input>
                </b-form-group>
                <b-form-group label="Phone">
                    <b-form-input v-model="form.hospice_phone"></b-form-input>
                </b-form-group>
            </template>
        </b-card>

        <!-- Do Not Resuscitate -->
        <b-card border-variant="secondary" header="Do Not Resuscitate (DNR)">
            <b-form-group label="Does the client have a DNR?" horizontal :label-cols="8" breakpoint="md">
                <b-form-radio-group v-model="form.dnr">
                    <b-form-radio :value="true">Yes</b-form-radio>
                    <b-form-radio :value="false">No</b-form-radio>
                </b-form-radio-group>
            </b-form-group>
            <b-form-group v-if="form.dnr" label="Where is the DNR posted?">
                <b-form-input v-model="form.dnr_location"></b-form-input>
            </b-form-group>
        </b-card>

        <!-- Emergency Contacts -->
        <b-row>
            <b-col lg="6">
                <b-card border-variant="secondary" header="Primary Emergency Contacts">
                    <b-form-group label="Name">
                        <b-form-input v-model="form.ec_name"></b-form-input>
                    </b-form-group>
                    <b-form-group label="Address">
                        <b-form-input v-model="form.ec_address"></b-form-input>
                    </b-form-group>
                    <b-form-group label="Phone Number">
                        <b-form-input v-model="form.ec_phone_number"></b-form-input>
                    </b-form-group>
                    <b-form-group label="Email">
                        <b-form-input v-model="form.ec_email"></b-form-input>
                    </b-form-group>
                    <b-form-group label="Relationship">
                        <b-form-input v-model="form.ec_relationship"></b-form-input>
                    </b-form-group>
                    <b-form-group label="POA" horizontal>
                        <b-form-radio-group v-model="form.ec_poa">
                            <b-form-radio :value="true">Yes</b-form-radio>
                            <b-form-radio :value="false">No</b-form-radio>
                        </b-form-radio-group>
                    </b-form-group>
                </b-card>
            </b-col>
            <b-col lg="6">
                <b-card border-variant="secondary" header="Secondary Emergency Contacts">
                    <b-form-group label="Name">
                        <b-form-input v-model="form.secondary_ec_name"></b-form-input>
                    </b-form-group>
                    <b-form-group label="Address">
                        <b-form-input v-model="form.secondary_ec_address"></b-form-input>
                    </b-form-group>
                    <b-form-group label="Phone Number">
                        <b-form-input v-model="form.secondary_ec_phone_number"></b-form-input>
                    </b-form-group>
                    <b-form-group label="Email">
                        <b-form-input v-model="form.secondary_ec_email"></b-form-input>
                    </b-form-group>
                    <b-form-group label="Relationship">
                        <b-form-input v-model="form.secondary_ec_relationship"></b-form-input>
                    </b-form-group>
                    <b-form-group label="POA" horizontal>
                        <b-form-radio-group v-model="form.secondary_ec_poa">
                            <b-form-radio :value="true">Yes</b-form-radio>
                            <b-form-radio :value="false">No</b-form-radio>
                        </b-form-radio-group>
                    </b-form-group>
                </b-card>
            </b-col>
        </b-row>

        <!-- Emergency Management Plan -->
        <b-card header="Emergency Management Plan">
            <p>In the event of a weather emergency or natural disaster...</p>
            <b-form-group label="Will the client leave the region?" horizontal :label-cols="8" breakpoint="md">
                <b-form-radio-group v-model="form.emp_leave_region">
                    <b-form-radio :value="true">Yes</b-form-radio>
                    <b-form-radio :value="false">No</b-form-radio>
                </b-form-radio-group>
            </b-form-group>
            <b-form-group v-if="form.emp_leave_region" label="If leaving, with whom and where?">
                <b-form-textarea v-model="form.emp_with_who_where"></b-form-textarea>
            </b-form-group>
            <b-form-group label="Will the client remain in his/her home?" horizontal :label-cols="8" breakpoint="md">
                <b-form-radio-group v-model="form.emp_remain_home">
                    <b-form-radio :value="true">Yes</b-form-radio>
                    <b-form-radio :value="false">No</b-form-radio>
                </b-form-radio-group>
            </b-form-group>
            <b-form-group label="Is the client going to a shelter?" horizontal :label-cols="8" breakpoint="md">
                <b-form-radio-group v-model="form.emp_shelter">
                    <b-form-radio :value="true">Yes</b-form-radio>
                    <b-form-radio :value="false">No</b-form-radio>
                </b-form-radio-group>
            </b-form-group>
            <b-form-group v-show="form.emp_shelter" label="What type of shelter?" horizontal :label-cols="8"
                          breakpoint="md">
                <b-form-radio-group v-model="form.emp_shelter_type">
                    <b-form-radio value="regular">Regular</b-form-radio>
                    <b-form-radio value="special_needs">Special Needs</b-form-radio>
                </b-form-radio-group>
            </b-form-group>
            <b-form-group v-show="form.emp_shelter" label="Where is the shelter?" horizontal :label-cols="8"
                          breakpoint="md">
                <b-form-textarea v-model="form.emp_shelter_address"></b-form-textarea>
            </b-form-group>
            <b-form-group v-show="form.emp_shelter" label="Would you like help with shelter registration?" horizontal
                          :label-cols="8" breakpoint="md">
                <b-form-radio-group v-model="form.emp_shelter_registration_assistance">
                    <b-form-radio :value="true">Yes</b-form-radio>
                    <b-form-radio :value="false">No</b-form-radio>
                </b-form-radio-group>
            </b-form-group>
            <b-form-group label="Who will be responsible for the client during an evacuation?">
                <b-form-input v-model="form.emp_evacuation_reponsible_party"></b-form-input>
            </b-form-group>
            <b-form-group label="Will the client need a caregiver during this period?" horizontal :label-cols="8"
                          breakpoint="md">
                <b-form-radio-group v-model="form.emp_caregiver_required">
                    <b-form-radio :value="true">Yes</b-form-radio>
                    <b-form-radio :value="false">No</b-form-radio>
                </b-form-radio-group>
            </b-form-group>
        </b-card>

        <!-- Preferences & Other Information -->
        <b-card border-variant="secondary" header="Preferences & Other Information">
            <b-form-group label="Caregiver Gender Preference" horizontal :label-cols="6" breakpoint="md">
                <b-form-radio-group v-model="form.gc_gender_pref">
                    <b-form-radio value="male">Male</b-form-radio>
                    <b-form-radio value="female">Female</b-form-radio>
                </b-form-radio-group>
            </b-form-group>
            <b-form-group label="Caregiver Attire Preference" horizontal :label-cols="6" breakpoint="md">
                <b-form-radio-group v-model="form.gc_attire_pref">
                    <b-form-radio value="scrubs">Scrubs</b-form-radio>
                    <b-form-radio value="business_casual">Business Casual</b-form-radio>
                </b-form-radio-group>
            </b-form-group>
            <b-form-group label="Does the client have pets?" horizontal :label-cols="6" breakpoint="md">
                <b-form-radio-group v-model="form.pets">
                    <b-form-radio :value="true">Yes</b-form-radio>
                    <b-form-radio :value="false">No</b-form-radio>
                </b-form-radio-group>
            </b-form-group>
            <b-form-group v-if="form.pets" label="What kind of pets and how many?">
                <b-form-textarea v-model="form.pets_description"></b-form-textarea>
            </b-form-group>
            <b-form-group label="Will the caregiver assist with pet care?" horizontal :label-cols="6" breakpoint="md">
                <b-form-radio-group v-model="form.cg_pet_assistance">
                    <b-form-radio :value="true">Yes</b-form-radio>
                    <b-form-radio :value="false">No</b-form-radio>
                </b-form-radio-group>
            </b-form-group>
            <b-form-group label="Will the client need assistance with transportation?" horizontal :label-cols="6"
                          breakpoint="md">
                <b-form-radio-group v-model="form.transportation">
                    <b-form-radio :value="true">Yes</b-form-radio>
                    <b-form-radio :value="false">No</b-form-radio>
                </b-form-radio-group>
            </b-form-group>
            <b-form-group v-if="form.transportation" label="Who's vehicle is preferred?" horizontal :label-cols="6"
                          breakpoint="md">
                <b-form-radio-group v-model="form.transportation_vehicle">
                    <b-form-radio value="caregiver">Caregiver</b-form-radio>
                    <b-form-radio value="client">Client</b-form-radio>
                </b-form-radio-group>
            </b-form-group>
        </b-card>

        <!-- Requested Start Date & Schedule -->
        <b-card border-variant="secondary" header="Requested Start Date & Schedule">
            <b-form-group label="Requested Start Date">
                <date-picker v-model="form.requested_start_at"></date-picker>
            </b-form-group>
            <b-form-group label="Requested Schedule">
                <b-form-textarea v-model="form.requested_schedule" :rows="3"></b-form-textarea>
            </b-form-group>
        </b-card>

        <b-row>
            <b-col>
                <b-btn type="submit" variant="primary">Next Step</b-btn>
            </b-col>
        </b-row>
    </form>
</template>

<script>
    export default {
        props: ['clientData', 'activities'],

        mixins: [],

        components: {},

        data() {
            return {
                form: {}
            }
        },

        created() {
            this.form = new Form({
                ...this.clientData,
                middle_initial: '',
                phone_number: '',
                address: '',
                facility: '',
                facility_instructions: '',
                primary_conditions: '',
                service_reasons: '',
                service_goals: '',
                allergies: '',
                medical_equipment: '',
                physician_name: '',
                physician_phone: '',
                physician_address: '',
                pharmacy_name: '',
                pharmacy_phone: '',
                pharmacy_address: '',
                hospice_care: '',
                hospice_office_location: '',
                hospice_case_manager: '',
                hospice_phone: '',
                dnr: '',
                dnr_location: '',
                ec_name: '',
                ec_phone_number: '',
                ec_email: '',
                ec_address: '',
                ec_relationship: '',
                ec_poa: '',
                secondary_ec_name: '',
                secondary_ec_phone_number: '',
                secondary_ec_email: '',
                secondary_ec_address: '',
                secondary_ec_relationship: '',
                secondary_ec_poa: '',
                emp_leave_region: '',
                emp_with_who_where: '',
                emp_remain_home: '',
                emp_shelter: '',
                emp_shelter_type: '',
                emp_shelter_address: '',
                emp_shelter_registration_assistance: '',
                emp_evacuation_reponsible_party: '',
                emp_caregiver_required: '',
                gc_gender_pref: '',
                gc_attire_pref: '',
                pets: '',
                pets_description: '',
                cg_pet_assistance: '',
                transportation: '',
                transportation_vehicle: '',
                requested_start_at: '',
                requested_shedule: '',
                activities: []
            })
        },

        mounted() {
        },

        computed: {},

        methods: {
            async submitStepOne() {
                let response = await this.form.post(`/business/clients/${this.clientData.id}/onboarding`)
                console.log(response);
            }
        }
    }
</script>

<style lang="scss">
</style>
