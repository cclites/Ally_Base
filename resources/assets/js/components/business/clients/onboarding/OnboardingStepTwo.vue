<template>
    <div>
        <!-- Client Personal Data -->
        <b-card border-variant="secondary" header="Client Personal Data">

            <b-row class="mb-2">
                <b-col>First Name</b-col>
                <b-col>{{ clientData.firstname }}</b-col>
            </b-row>

            <b-row class="mb-2">
                <b-col>Middle Initial</b-col>
                <b-col>{{ onboarding.middle_initial }}</b-col>
            </b-row>

            <b-row class="mb-2">
                <b-col>Last Name</b-col>
                <b-col>{{ clientData.lastname }}</b-col>
            </b-row>

            <b-row class="mb-2">
                <b-col>Email</b-col>
                <b-col>{{ clientData.email }}</b-col>
            </b-row>

            <b-row class="mb-2">
                <b-col>Phone Number</b-col>
                <b-col>{{ onboarding.phone_number }}</b-col>
            </b-row>

            <b-row class="mb-2">
                <b-col>Date of Birth</b-col>
                <b-col>{{ clientData.date_of_birth }}</b-col>
            </b-row>

            <b-row class="mb-2">
                <b-col>Gender</b-col>
                <b-col>{{ clientData.gender }}</b-col>
            </b-row>

            <b-row class="mb-2">
                <b-col>Address</b-col>
                <b-col>{{ onboarding.address }}</b-col>
            </b-row>

            <b-row class="mb-2">
                <b-col>Does the client live in a facility or subdivision?</b-col>
                <b-col>
                    <span v-if="onboarding.facility">Yes</span>
                    <span v-else>No</span>
                    <div v-if="onboarding.facility && onboarding.facility_instructions">
                        {{ onboarding.facility_instructions }}
                    </div>
                </b-col>
            </b-row>
        </b-card>

        <!-- General Information -->
        <b-card border-variant="secondary" header="General Information">
            <b-row class="mb-2">
                <b-col>Primary Medical Condition(s)</b-col>
                <b-col>{{ onboarding.primary_conditions }}</b-col>
            </b-row>
            <b-row class="mb-2">
                <b-col>Reason(s) for Service</b-col>
                <b-col>{{ onboarding.service_reasons }}</b-col>
            </b-row>
            <b-row class="mb-2">
                <b-col>Goal(s) of Service</b-col>
                <b-col>{{ onboarding.service_goals }}</b-col>
            </b-row>
            <b-row class="mb-2">
                <b-col>Allergies (Food/Medication)</b-col>
                <b-col>{{ onboarding.allergies }}</b-col>
            </b-row>
            <b-row class="mb-2">
                <b-col>Medical Equipment in the Home (ex: wheelchair, walker, oxygen)</b-col>
                <b-col>{{ onboarding.medical_equipment }}</b-col>
            </b-row>
            <b-row class="mb-2">
                <b-col>Client Height</b-col>
                <b-col>{{ onboarding.height }}</b-col>
            </b-row>
            <b-row class="mb-2">
                <b-col>Client Weight</b-col>
                <b-col>{{ onboarding.weight }}</b-col>
            </b-row>
        </b-card>

        <!-- Hands-On Care Activities -->
        <b-card border-variant="secondary" header="Hands-On Care Activities Requested">
            <template v-for="item in onboarding.activities">
                <b-row class="mb-2" v-if="item.category === 'hands_on'">
                    <b-col>{{ item.name }}</b-col>
                    <b-col>{{ item.pivot.assistance_level }}</b-col>
                </b-row>
            </template>
        </b-card>

        <!-- Household Care Activities -->
        <b-card border-variant="secondary" header="Household Activities Requested">
            <template v-for="item in onboarding.activities">
                <b-row class="mb-2" v-if="item.category === 'household'">
                    <b-col>{{ item.name }}</b-col>
                    <b-col>{{ item.pivot.assistance_level }}</b-col>
                </b-row>
            </template>
        </b-card>

        <!-- Primary Care Physician & Pharmacy -->
        <b-row>
            <b-col lg="6">
                <b-card border-variant="secondary" header="Primary Care Physician">
                    <b-row class="mb-2">
                        <b-col>Name</b-col>
                        <b-col>{{ onboarding.physician_name }}</b-col>
                    </b-row>
                    <b-row class="mb-2">
                        <b-col>Phone</b-col>
                        <b-col>{{ onboarding.physician_phone }}</b-col>
                    </b-row>
                    <b-row class="mb-2">
                        <b-col>Address</b-col>
                        <b-col>{{ onboarding.physician_address }}</b-col>
                    </b-row>
                </b-card>
            </b-col>
            <b-col lg="6">
                <b-card border-variant="secondary" header="Pharmacy">
                    <b-row class="mb-2">
                        <b-col>Name</b-col>
                        <b-col>{{ onboarding.pharmacy_name }}</b-col>
                    </b-row>
                    <b-row class="mb-2">
                        <b-col>Phone</b-col>
                        <b-col>{{ onboarding.pharmacy_phone }}</b-col>
                    </b-row>
                    <b-row class="mb-2">
                        <b-col>Address</b-col>
                        <b-col>{{ onboarding.pharmacy_address }}</b-col>
                    </b-row>
                </b-card>
            </b-col>
        </b-row>

        <!-- Prescription Medication List -->
        <b-card border-variant="secondary" header="Prescription Medication List">
            <b-row class="mb-3">
                <b-col md="4">
                    Type
                </b-col>
                <b-col md="4">
                    Dose
                </b-col>
                <b-col md="4">
                    Frequency
                </b-col>
            </b-row>
            <b-row v-for="med in onboarding.client.medications" :key="med.id">
                <b-col md="4">
                    {{ med.type }}
                </b-col>
                <b-col md="4">
                    {{ med.dose }}
                </b-col>
                <b-col md="4">
                    {{ med.frequency }}
                </b-col>
            </b-row>
        </b-card>

        <!-- Hospice Information -->
        <b-card border-variant="secondary" header="Hospice Information">
            <b-row class="mb-2">
                <b-col>Is the client under Hospice Care?</b-col>
                <b-col>
                    <span v-if="onboarding.hospice_care">Yes</span>
                    <span v-else>No</span>
                </b-col>
            </b-row>

            <template v-if="onboarding.hospice_care">
                <b-row class="mb-2">
                    <b-col>Office Location (city)</b-col>
                    <b-col>{{ onboarding.hospice_office_location }}</b-col>
                </b-row>
                <b-row class="mb-2">
                    <b-col>Client Service Coordinator Name</b-col>
                    <b-col>{{ onboarding.hospice_case_manager }}</b-col>
                </b-row>
                <b-row class="mb-2">
                    <b-col>Phone</b-col>
                    <b-col>{{ onboarding.hospice_phone }}</b-col>
                </b-row>
            </template>
        </b-card>

        <!-- Do Not Resuscitate -->
        <b-card border-variant="secondary" header="Do Not Resuscitate (DNR)">
            <b-row class="mb-2">
                <b-col>Does the client have a DNR?</b-col>
                <b-col>
                    <span v-if="onboarding.dnr">Yes</span>
                    <span v-else>No</span>
                </b-col>
            </b-row>
            <b-row class="mb-2" v-if="onboarding.dnr">
                <b-col>Where is the DNR posted?</b-col>
                <b-col>{{ onboarding.dnr_location }}</b-col>
            </b-row>
        </b-card>

        <!-- Emergency Contacts -->
        <b-row>
            <b-col lg="6">
                <b-card border-variant="secondary" header="Primary Emergency Contacts">
                    <b-row class="mb-2">
                        <b-col>Name</b-col>
                        <b-col>{{ onboarding.ec_name }}</b-col>
                    </b-row>
                    <b-row class="mb-2">
                        <b-col>Address</b-col>
                        <b-col>{{ onboarding.ec_address }}</b-col>
                    </b-row>
                    <b-row class="mb-2">
                        <b-col>Phone</b-col>
                        <b-col>{{ onboarding.ec_phone_number }}</b-col>
                    </b-row>
                    <b-row class="mb-2">
                        <b-col>Email</b-col>
                        <b-col>{{ onboarding.ec_email }}</b-col>
                    </b-row>
                    <b-row class="mb-2">
                        <b-col>Relationship</b-col>
                        <b-col>{{ onboarding.ec_relationship }}</b-col>
                    </b-row>
                    <b-row class="mb-2">
                        <b-col>POA</b-col>
                        <b-col>
                            <span v-if="onboarding.ec_poa">Yes</span>
                            <span v-else>No</span>
                        </b-col>
                    </b-row>
                </b-card>
            </b-col>
            <b-col lg="6">
                <b-card border-variant="secondary" header="Secondary Emergency Contacts">
                    <b-row class="mb-2">
                        <b-col>Name</b-col>
                        <b-col>{{ onboarding.secondary_ec_name }}</b-col>
                    </b-row>
                    <b-row class="mb-2">
                        <b-col>Address</b-col>
                        <b-col>{{ onboarding.secondary_ec_address }}</b-col>
                    </b-row>
                    <b-row class="mb-2">
                        <b-col>Phone</b-col>
                        <b-col>{{ onboarding.secondary_ec_phone_number }}</b-col>
                    </b-row>
                    <b-row class="mb-2">
                        <b-col>Email</b-col>
                        <b-col>{{ onboarding.secondary_ec_email }}</b-col>
                    </b-row>
                    <b-row class="mb-2">
                        <b-col>Relationship</b-col>
                        <b-col>{{ onboarding.secondary_ec_relationship }}</b-col>
                    </b-row>
                    <b-row class="mb-2">
                        <b-col>POA</b-col>
                        <b-col>
                            <span v-if="onboarding.secondary_ec_poa">Yes</span>
                            <span v-else>No</span>
                        </b-col>
                    </b-row>
                </b-card>
            </b-col>
        </b-row>

        <!-- Emergency Management Plan -->
        <b-card header="Emergency Management Plan">
            <p>In the event of a weather emergency or natural disaster...</p>
            <b-row class="mb-2">
                <b-col>Will the client leave the region?</b-col>
                <b-col>
                    <span v-if="onboarding.emp_leave_region">Yes</span>
                    <span v-else>No</span>
                </b-col>
            </b-row>
            <b-row class="mb-2">
                <b-col>If leaving, with whom and where?</b-col>
                <b-col>
                    {{ onboarding.emp_with_who_where }}
                </b-col>
            </b-row>
            <b-row class="mb-2">
                <b-col>Will the client remain in his/her home?</b-col>
                <b-col>
                    <span v-if="onboarding.emp_remain_home">Yes</span>
                    <span v-else>No</span>
                </b-col>
            </b-row>
            <b-row class="mb-2">
                <b-col>Is the client going to a shelter?</b-col>
                <b-col>
                    <span v-if="onboarding.emp_shelter">Yes</span>
                    <span v-else>No</span>
                </b-col>
            </b-row>
            <b-row class="mb-2" v-if="onboarding.emp_shelter">
                <b-col>What type of shelter?</b-col>
                <b-col>{{ onboarding.emp_shelter_type }}</b-col>
            </b-row>
            <b-row class="mb-2">
                <b-col>Where is the shelter?</b-col>
                <b-col>{{ onboarding.emp_shelter_address }}</b-col>
            </b-row>
            <b-row class="mb-2">
                <b-col>Would you like help with shelter registration?</b-col>
                <b-col>
                    <span v-if="onboarding.emp_shelter_registration_assistance">Yes</span>
                    <span v-else>No</span>
                </b-col>
            </b-row>
            <b-row class="mb-2">
                <b-col>Who will be responsible for the client during an evacuation?</b-col>
                <b-col>{{ onboarding.emp_evacuation_responsible_party }}</b-col>
            </b-row>

            <b-row class="mb-2">
                <b-col>Will the client need a caregiver during this period?</b-col>
                <b-col>
                    <span v-if="onboarding.emp_caregiver_required">Yes</span>
                    <span v-else>No</span>
                </b-col>
            </b-row>
        </b-card>

        <!-- Preferences & Other Information -->
        <b-card border-variant="secondary" header="Preferences & Other Information">
            <b-row class="mb-2">
                <b-col>Caregiver Gender Preference</b-col>
                <b-col>
                    <span v-if="onboarding.cg_gender_pref === 'M'">Male</span>
                    <span v-if="onboarding.cg_gender_pref === 'F'">Female</span>
                </b-col>
            </b-row>
            <b-row class="mb-2">
                <b-col>Caregiver Attire Preference</b-col>
                <b-col>
                    <span v-if="onboarding.cg_attire_pref === 'scrubs'">Scrubs</span>
                    <span v-if="onboarding.cg_attire_pref === 'business_casual'">Business Casual</span>
                </b-col>
            </b-row>
            <b-row class="mb-2">
                <b-col>Does the client have pets?</b-col>
                <b-col>
                    <span v-if="onboarding.pets">Yes</span>
                    <span v-else>No</span>
                </b-col>
            </b-row>
            <b-row class="mb-2" v-if="onboarding.pets">
                <b-col>What kind of pets and how many?</b-col>
                <b-col>{{ onboarding.pets_description }}</b-col>
            </b-row>
            <b-row class="mb-2" v-if="onboarding.pets">
                <b-col>Will the caregiver assist with pet care?</b-col>
                <b-col>
                    <span v-if="onboarding.cg_pet_assistance">Yes</span>
                    <span v-else>No</span>
                </b-col>
            </b-row>
            <b-row class="mb-2" v-if="onboarding.transportation">
                <b-col>Will the client need assistance with transportation?</b-col>
                <b-col>
                    <span v-if="onboarding.transportation">Yes</span>
                    <span v-else>No</span>
                </b-col>
            </b-row>
            <b-row class="mb-2" v-if="onboarding.transportation">
                <b-col>Who's vehicle is preferred?</b-col>
                <b-col>
                    {{ onboarding.transportation_vehicle }}
                </b-col>
            </b-row>
        </b-card>

        <!-- Requested Start Date & Schedule -->
        <b-card border-variant="secondary" header="Requested Start Date & Schedule">
            <b-row class="mb-2">
                <b-col>Requested Start Date</b-col>
                <b-col>{{ requestedStartDate }}</b-col>
            </b-row>
            <b-row class="mb-2">
                <b-col>Requested Schedule</b-col>
                <b-col>{{ onboarding.requested_schedule }}</b-col>
            </b-row>
        </b-card>

        <div v-html="onboarding.signature.content" class="img-responsive"></div>

        <b-row class="mt-3">
            <b-col>
                <!--<b-btn :disabled="state === 'updating'" variant="secondary" @click="previousStep">Previous Step</b-btn>-->
                <b-btn class="mr-2" :disabled="state === 'updating'" @click="nextStep">Next Step</b-btn>
                <i class="fa fa-spin fa-spinner" v-show="state === 'updating'"></i>
            </b-col>
        </b-row>

    </div>
</template>

<script>
    export default {
        props: ['onboarding', 'clientData'],

        data() {
            return {
                form: new Form({
                    onboarding_step: 3
                }),
                state: ''
            }
        },

        computed: {
          requestedStartDate() {
              let date = _.isObject(this.onboarding.requested_start_at) ? this.onboarding.requested_start_at : this.onboarding.requested_start_at;
              if (date) {
                  return moment(date).format('L');
              }
              return '';
          }
        },

        methods: {
            async nextStep() {
                this.state = 'updating';
                let response = await this.form.put(`/business/clients/onboarding/${this.onboarding.id}`);
                this.$emit('next', response.data);
                this.state = '';
            },

            previousStep() {
                this.$emit('previous');
            }
        }
    }
</script>

<style lang="scss">
    .img-responsive {
        max-width: 300px;
        display: flex;
    }
</style>
