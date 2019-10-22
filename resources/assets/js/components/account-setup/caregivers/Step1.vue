<template>
    <div>
        <loading-card v-if="loading" text="Loading account information..."></loading-card>
        <form v-else @submit.prevent="submit()" @keydown="form.clearError($event.target.name)">
            <b-card header="Please confirm your information"
                    header-bg-variant="info"
                    header-text-variant="white"
            >
                <b-row>
                    <b-col lg="6">
                        <b-form-group label="First Name" label-for="firstname" label-class="required">
                            <b-form-input
                                    id="firstname"
                                    name="firstname"
                                    type="text"
                                    v-model="form.firstname"
                                    required
                                    :disabled="busy"
                            >
                            </b-form-input>
                            <input-help :form="form" field="firstname" text="Confirm your first name."></input-help>
                        </b-form-group>
                        <b-form-group label="Last Name" label-for="lastname" label-class="required">
                            <b-form-input
                                    id="lastname"
                                    name="lastname"
                                    type="text"
                                    v-model="form.lastname"
                                    required
                                    :disabled="busy"
                            >
                            </b-form-input>
                            <input-help :form="form" field="lastname" text="Confirm your last name."></input-help>
                        </b-form-group>
                        <b-form-group label="Email Address" label-for="email" label-class="required">
                            <b-form-input
                                    id="email"
                                    name="email"
                                    type="email"
                                    v-model="form.email"
                                    :disabled="busy"
                            >
                            </b-form-input>
                            <input-help :form="form" field="email" text="Confirm your email address.  Ex: user@domain.com"></input-help>
                        </b-form-group>
                    </b-col>
                    <b-col lg="6">
                        <b-form-group label="Date of Birth" label-for="date_of_birth" label-class="required">
                            <mask-input v-model="form.date_of_birth" id="date_of_birth" type="date" :disabled="busy"></mask-input>
                            <input-help :form="form" field="date_of_birth" text="Confirm your date of birth. Ex: MM/DD/YYYY"></input-help>
                        </b-form-group>
                        <b-form-group label="Social Security Number" label-for="ssn" label-class="required">
                            <mask-input v-model="form.ssn" id="ssn" name="ssn" type="ssn" :disabled="busy"></mask-input>
                            <input-help :form="form" field="ssn" text="Enter the client's social security number."></input-help>
                        </b-form-group>
                        <b-form-group label="Phone Number" label-for="phone_number" label-class="required">
                            <mask-input v-model="form.phone_number" id="phone_number" type="phone" :disabled="busy"></mask-input>
                            <input-help :form="form" field="phone_number" text="Confirm your full phone number."></input-help>
                        </b-form-group>
                        <b-form-group label="Address" label-class="required">
                            <b-row>
                                <b-col sm="12">
                                    <b-form-input
                                            id="address1"
                                            name="address1"
                                            type="text"
                                            v-model="form.address1"
                                            :disabled="busy"
                                    >
                                    </b-form-input>
                                    <input-help :form="form" field="address1" text="Confirm your street address."></input-help>
                                </b-col>
                            </b-row>
                            <b-row>
                                <b-col sm="12">
                                    <b-form-input
                                            id="address2"
                                            name="address2"
                                            type="text"
                                            v-model="form.address2"
                                            :disabled="busy"
                                    >
                                    </b-form-input>
                                    <input-help :form="form" field="address2" text="Confirm your address second line (apartment number or other)."></input-help>
                                </b-col>
                            </b-row>
                            <b-row>
                                <b-col sm="4" xs="5">
                                    <b-form-input
                                            id="city"
                                            name="city"
                                            type="text"
                                            v-model="form.city"
                                            :disabled="busy"
                                    >
                                    </b-form-input>
                                    <input-help :form="form" field="city" text="Confirm your city."></input-help>
                                </b-col>
                                <b-col sm="2" xs="1">
                                    <b-form-input
                                            id="state"
                                            name="state"
                                            type="text"
                                            v-model="form.state"
                                            :disabled="busy"
                                    >
                                    </b-form-input>
                                    <input-help :form="form" field="state" text="Confirm your state."></input-help>
                                </b-col>
                                <b-col sm="3" xs="3">
                                    <b-form-input
                                            id="zip"
                                            name="zip"
                                            type="text"
                                            v-model="form.zip"
                                            :disabled="busy"
                                    >
                                    </b-form-input>
                                    <input-help :form="form" field="zip" text="Confirm your zip."></input-help>
                                </b-col>
                            </b-row>
                        </b-form-group>
                    </b-col>
                </b-row>
            </b-card>
            <b-row>
                <b-col lg="12" class="text-right">
                    <b-button variant="success" size="lg" type="submit" :disabled="busy">
                        <i v-if="busy" class="fa fa-spinner fa-spin mr-2" size="lg"></i>
                        Save and Continue to Next Step
                    </b-button>
                </b-col>
            </b-row>
        </form>
    </div>
</template>

<script>
    export default {
        props: {
            'token': {},
            'caregiver': {},
        },

        data() {
            return {
                busy: false,
                loading: false,
                terms: '',
                form: new Form({
                    firstname: this.caregiver.firstname,
                    lastname: this.caregiver.lastname,
                    email: this.caregiver.email,
                    date_of_birth: moment(this.caregiver.user.date_of_birth).format('L'),
                    phone_number: this.caregiver.phone_number ? this.caregiver.phone_number.national_number : '',
                    address1: this.caregiver.address ? this.caregiver.address.address1 : '',
                    address2: this.caregiver.address ? this.caregiver.address.address2 : '',
                    city: this.caregiver.address ? this.caregiver.address.city : '',
                    county: this.caregiver.address ? this.caregiver.address.county : '',
                    state: this.caregiver.address ? this.caregiver.address.state : '',
                    zip: this.caregiver.address ? this.caregiver.address.zip : '',
                    country: 'US', // This form assumes US addresses ONLY!
                    accepted_terms: 0,
                    ssn: this.caregiver.ssn ? this.caregiver.masked_ssn : ''
                })
            }
        },

        async mounted() {
            this.loading = false;
        },

        methods: {
            submit() {
                this.busy = true;
                this.form.post(`/account-setup/caregivers/${this.token}/step1`)
                    .then( ({ data }) => {
                        this.$emit('updated', data.data);
                    })
                    .catch(e => {
                    })
                    .finally(() => {
                        this.busy = false;
                    });
            }
        }
    }
</script>
