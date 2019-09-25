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
                        <b-form-group label="Date of Birth" label-for="date_of_birth">
                            <mask-input v-model="form.date_of_birth" id="date_of_birth" type="date" :disabled="busy"></mask-input>
                            <input-help :form="form" field="date_of_birth" text="Confirm your date of birth. Ex: MM/DD/YYYY"></input-help>
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
            <b-card header="Terms of Service"
                    header-bg-variant="info"
                    header-text-variant="white"
            >
                <div class="d-flex mb-2">
                    <b-btn variant="info" :href="terms_url" target="_blank" class="ml-auto"><i class="fa fa-print" />Print</b-btn>
                </div>
                <div v-html="terms" style="overflow-y: scroll;max-height:220px;"></div>
            </b-card>
            <b-row>
                <b-col lg="12" class="text-right">
                    <div class="form-check">
                        <label class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" name="accepted_terms" v-model="form.accepted_terms" value="1" :disabled="busy">
                            <span class="custom-control-indicator"></span>
                            <span class="custom-control-description"><b>I accept the terms and conditions above</b></span>
                        </label>
                        <input-help :form="form" field="accepted_terms" text=""></input-help>
                    </div>
                    <b-button variant="success" size="lg" type="submit" :disabled="busy">
                        <i v-if="busy" class="fa fa-spinner fa-spin mr-2" size="lg"></i>
                        Accept and Continue to Next Step
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
            'client': {},
        },

        data() {
            return {
                busy: false,
                loading: false,
                terms: '',
                terms_url: '',
                form: new Form({
                    firstname: this.client.firstname,
                    lastname: this.client.lastname,
                    email: this.client.email,
                    date_of_birth: moment(this.client.user.date_of_birth).format('L'),
                    phone_number: this.client.phone_number ? this.client.phone_number.national_number : '',
                    address1: this.client.address ? this.client.address.address1 : '',
                    address2: this.client.address ? this.client.address.address2 : '',
                    city: this.client.address ? this.client.address.city : '',
                    county: this.client.address ? this.client.address.county : '',
                    state: this.client.address ? this.client.address.state : '',
                    zip: this.client.address ? this.client.address.zip : '',
                    country: 'US', // This form assumes US addresses ONLY!
                    accepted_terms: 0,
                })
            }
        },

        async mounted() {
            this.loading = true;
            await this.fetchTerms();
            this.loading = false;
        },

        methods: {
            async fetchTerms() {
                let response = await axios.get(`/account-setup/clients/${this.token}/terms`);
                this.terms = response.data.terms;
                this.terms_url = response.data.terms_url;
            },

            submit() {
                this.busy = true;
                this.form.post(`/account-setup/clients/${this.token}/step1`)
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
