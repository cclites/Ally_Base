<template>
    <b-card header="New Provider"
        header-bg-variant="info"
        header-text-variant="white"
        >
        <form @submit.prevent="submitForm()" @keydown="form.clearError($event.target.name)">
            <b-row>
                <b-col lg="12">
                    <h4>Provider Details</h4>
                </b-col>
            </b-row>
            <b-row>
                <b-col lg="6">
                    <b-form-group label="Provider Name" label-for="name">
                        <b-form-input
                                id="name"
                                name="name"
                                type="text"
                                v-model="form.name"
                                required
                        >
                        </b-form-input>
                        <input-help :form="form" field="name" text="Enter the provider name."></input-help>
                    </b-form-group>
                    <b-form-group label="Phone Number" label-for="phone1">
                        <b-form-input
                                id="phone1"
                                name="phone1"
                                type="text"
                                v-model="form.phone1"
                                required
                        >
                        </b-form-input>
                        <input-help :form="form" field="phone1" text="Enter their phone number."></input-help>
                    </b-form-group>
                    <b-form-group label="Time Zone" label-for="timezone">
                        <b-form-select
                            id="timezone"
                            name="timezone"
                            :options="['America/New_York', 'America/Chicago', 'America/Denver', 'America/Phoenix', 'America/Los_Angeles']"
                            v-model="form.timezone"
                            >
                        </b-form-select>
                        <input-help :form="form" field="timezone" text="Select the city that matches their timezone."></input-help>
                    </b-form-group>
                    <b-form-group label="Multi Location Registry" label-for="multi_location_registry">
                        <b-form-select
                            id="multi_location_registry"
                            name="multi_location_registry"
                            v-model="form.multi_location_registry">

                            <option value="no">No</option>
                            <option value="yes">Yes</option>
                        </b-form-select>
                        <input-help :form="form" field="multi_location_registry" text="Select multi location registry "></input-help>
                    </b-form-group>
                </b-col>
                <b-col lg="6">
                    <b-form-group label="Street Address" label-for="address1">
                        <b-form-input
                            id="address1"
                            name="address1"
                            type="text"
                            v-model="form.address1"
                            >
                        </b-form-input>
                        <input-help :form="form" field="address1" text="Enter their street address."></input-help>
                    </b-form-group>
                    <b-form-group label="City" label-for="city">
                        <b-form-input
                            id="city"
                            name="city"
                            type="text"
                            v-model="form.city"
                            >
                        </b-form-input>
                        <input-help :form="form" field="city" text="Enter their city."></input-help>
                    </b-form-group>
                    <b-form-group label="State" label-for="state">
                        <b-form-input
                            id="state"
                            name="state"
                            type="text"
                            maxlength="2"
                            v-model="form.state"
                            >
                        </b-form-input>
                        <input-help :form="form" field="state" text="Enter their state abbreviation."></input-help>
                    </b-form-group>
                    <b-form-group label="Zip Code" label-for="zip">
                        <b-form-input
                            id="zip"
                            name="zip"
                            type="text"
                            v-model="form.zip"
                            >
                        </b-form-input>
                        <input-help :form="form" field="zip" text="Enter their zip code."></input-help>
                    </b-form-group>
                </b-col>
            </b-row>
            <b-row>
                <b-col lg="12">
                    <hr />
                    <h4>Office User Details</h4>
                </b-col>
            </b-row>
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
                        <input-help :form="form" field="firstname" text="Enter their first name."></input-help>
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
                        <input-help :form="form" field="lastname" text="Enter their last name."></input-help>
                    </b-form-group>
                    <b-form-group label="Email Address" label-for="email">
                        <b-form-input
                                id="email"
                                name="email"
                                type="email"
                                v-model="form.email"
                                @change="copyEmailToUsername()"
                        >
                        </b-form-input>
                        <input-help :form="form" field="email" text="Enter their email address.  Ex: user@domain.com"></input-help>
                    </b-form-group>
                    <b-form-group label="Username" label-for="username">
                        <b-form-input
                                id="username"
                                name="username"
                                type="text"
                                v-model="form.username"
                        >
                        </b-form-input>
                        <input-help :form="form" field="username" text="Enter their username to be used for logins."></input-help>
                    </b-form-group>
                </b-col>
                <b-col lg="6">
                    <b-form-group label="Password" label-for="password">
                        <b-form-input
                                id="password"
                                name="ssn"
                                type="password"
                                v-model="form.password"
                        >
                        </b-form-input>
                        <input-help :form="form" field="password" text="Enter the password they will use to login for the first time."></input-help>
                    </b-form-group>
                    <b-form-group label="Confirm Password" label-for="password_confirmation">
                        <b-form-input
                                id="password_confirmation"
                                name="ssn"
                                type="password"
                                v-model="form.password_confirmation"
                        >
                        </b-form-input>
                        <input-help :form="form" field="password_confirmation" text="Re-enter the above password."></input-help>
                    </b-form-group>
                </b-col>
            </b-row>
            <b-row>
                <b-col lg="12">
                    <b-button id="save-business" variant="success" type="submit">Create &amp; Continue</b-button>
                </b-col>
            </b-row>
        </form>
    </b-card>
</template>

<script>
    export default {
        props: {},

        data() {
            return {
                form: new Form({
                    name: null,
                    phone1: null,
                    timezone: 'America/New_York',
                    multi_location_registry: 'no',
                    address1: null,
                    city: null,
                    state: null,
                    zip: null,
                    //
                    firstname: null,
                    lastname: null,
                    email: null,
                    username: null,
                    password: null,
                    password_confirmation: null,
                })
            }
        },

        mounted() {
        },

        methods: {
            copyEmailToUsername() {
                if (this.form.email && !this.form.username) {
                    this.form.username = this.form.email;
                }
            },

            submitForm() {
                this.form.post('/admin/businesses');
            }
        }
    }
</script>
