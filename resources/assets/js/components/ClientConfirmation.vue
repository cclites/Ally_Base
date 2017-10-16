<template>
    <form @submit.prevent="submitForm()" @keydown="form.clearError($event.target.name)">

        <b-card header="Please confirm your information"
                header-bg-variant="info"
                header-text-variant="white"
        >
            <b-row>
                <b-col lg="6">
                    <b-form-group label="First Name" label-for="firstname">
                        <b-form-input
                                id="firstname"
                                name="firstname"
                                type="text"
                                v-model="form.firstname"
                                required
                        >
                        </b-form-input>
                        <input-help :form="form" field="firstname" text="Confirm your first name."></input-help>
                    </b-form-group>
                    <b-form-group label="Last Name" label-for="lastname">
                        <b-form-input
                                id="lastname"
                                name="lastname"
                                type="text"
                                v-model="form.lastname"
                                required
                        >
                        </b-form-input>
                        <input-help :form="form" field="lastname" text="Confirm your last name."></input-help>
                    </b-form-group>
                    <b-form-group label="Date of Birth" label-for="date_of_birth">
                        <b-form-input
                                id="date_of_birth"
                                name="date_of_birth"
                                type="text"
                                v-model="form.date_of_birth"
                        >
                        </b-form-input>
                        <input-help :form="form" field="date_of_birth" text="Confirm your date of birth. Ex: MM/DD/YYYY"></input-help>
                    </b-form-group>
                    <b-form-group label="Email Address" label-for="email">
                        <b-form-input
                                id="email"
                                name="email"
                                type="email"
                                v-model="form.email"
                        >
                        </b-form-input>
                        <input-help :form="form" field="email" text="Confirm your email address.  Ex: user@domain.com"></input-help>
                    </b-form-group>
                </b-col>
                <b-col lg="6">
                    <b-form-group label="Phone Number" label-for="phone_number">
                        <b-form-input
                                id="phone_number"
                                name="phone_number"
                                type="text"
                                v-model="form.phone_number"
                        >
                        </b-form-input>
                        <input-help :form="form" field="phone_number" text="Confirm your full phone number."></input-help>
                    </b-form-group>
                    <b-form-group label="Address">
                        <b-row>
                            <b-col sm="12">
                                <b-form-input
                                        id="address1"
                                        name="address1"
                                        type="text"
                                        v-model="form.address1"
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
                                >
                                </b-form-input>
                                <input-help :form="form" field="address2" text="Confirm your address second line (apartment number or other)."></input-help>
                            </b-col>
                        </b-row>
                        <b-row>
                            <b-col sm="5" xs="6">
                                <b-form-input
                                        id="city"
                                        name="city"
                                        type="text"
                                        v-model="form.city"
                                >
                                </b-form-input>
                                <input-help :form="form" field="city" text="Confirm your city."></input-help>
                            </b-col>
                            <b-col sm="3" xs="2">
                                <b-form-input
                                        id="state"
                                        name="state"
                                        type="text"
                                        v-model="form.state"
                                >
                                </b-form-input>
                                <input-help :form="form" field="state" text="Confirm your state."></input-help>
                            </b-col>
                            <b-col sm="4" xs="4">
                                <b-form-input
                                        id="zip"
                                        name="zip"
                                        type="text"
                                        v-model="form.zip"
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
            <div class="embed-responsive" style="height:200px;">
                <iframe class="embed-responsive-item" src="/terms-inc.html"></iframe>
            </div>
        </b-card>
        <b-row>
            <b-col lg="12" class="text-right">
                <div class="form-check">
                    <label class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" name="accepted_terms" v-model="form.accepted_terms" value="1">
                        <span class="custom-control-indicator"></span>
                        <span class="custom-control-description"><b>I accept the terms and conditions above</b></span>
                    </label>
                    <input-help :form="form" field="accepted_terms" text=""></input-help>
                </div>
                <b-button variant="success" type="submit">Accept and Verify</b-button>
            </b-col>
        </b-row>
    </form>
</template>

<script>
    export default {
        props: {
            'id': {},
            'client': {},
            'user': {},
            'phoneNumber': {},
            'address': {},
        },

        data() {
            return {
                form: new Form({
                    firstname: this.user.firstname,
                    lastname: this.user.lastname,
                    email: this.user.email,
                    date_of_birth: moment(this.user.date_of_birth).format('L'),
                    phone_number: this.phoneNumber,
                    address1: this.address.address1,
                    address2: this.address.address2,
                    city: this.address.city,
                    state: this.address.state,
                    zip: this.address.zip,
                    country: 'US', // This form assumes US addresses ONLY!
                    accepted_terms: 0,
                })
            }
        },

        mounted() {

        },

        methods: {

            submitForm() {
                this.form.post('/reconfirm/' + this.id)
                    .then(function(response) {
                        window.location = '/reconfirm/saved';
                    });
            }

        }


    }
</script>
