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
                    <b-form-group label="Full Name" label-for="name" label-class="required">
                        <b-form-input
                                id="name"
                                name="name"
                                type="text"
                                v-model="form.name"
                                required
                        >
                        </b-form-input>
                        <input-help :form="form" field="name" text="Enter the full name. (Shows on statements)"></input-help>
                    </b-form-group>
                    <b-form-group label="Short Name" label-for="short_name" label-class="required">
                        <b-form-input
                                id="short_name"
                                name="short_name"
                                type="text"
                                v-model="form.short_name"
                                required
                        >
                        </b-form-input>
                        <input-help :form="form" field="name" text="Enter the short name (Shows in location dropdowns)."></input-help>
                    </b-form-group>
                    <b-form-group label="Phone Number" label-for="phone1" label-class="required">
                        <mask-input type="phone"
                                    id="phone1"
                                    name="phone1"
                                    v-model="form.phone1"
                                    required
                        >
                        </mask-input>
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
                    <b-form-group label="Business Chain" label-for="chain_id">
                        <b-form-select
                            id="chain_id"
                            name="chain_id"
                            v-model="form.chain_id">

                            <option value="">--Select--</option>
                            <option :value="null">New Business Chain</option>
                            <option v-for="chain in chains" :value="chain.id">{{ chain.name }}</option>
                        </b-form-select>
                        <input-help :form="form" field="chain_id" text=""></input-help>
                    </b-form-group>
                    <b-form-group v-if="form.chain_id === null">
                        <b-form-input
                                type="text"
                                v-model="form.new_chain_name"
                                required
                        >
                        </b-form-input>
                        <input-help :form="form" field="new_chain_name" text="Enter the new business chain name"></input-help>
                    </b-form-group>
                    <input-help v-if="form.chain_id !== null" :form="form" field="new_chain_name" text=""></input-help>
                </b-col>
                <b-col lg="6">
                    <b-form-group label="Type" label-for="type">
                        <b-form-select
                                id="type"
                                name="type"
                                v-model="form.type">

                            <option value="Registry">Registry</option>
                            <option value="Agency">Agency</option>
                            <option value="DRA">DRA</option>
                            <option value="Franchisor">Franchisor</option>
                        </b-form-select>
                        <input-help :form="form" field="type" text="Select provider type"></input-help>
                    </b-form-group>
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
                    <b-button id="save-business" variant="success" type="submit">Create &amp; Continue</b-button>
                </b-col>
            </b-row>
        </form>
    </b-card>
</template>

<script>
    export default {
        props: ['chains'],

        data() {
            return {
                form: new Form({
                    name: "",
                    short_name: "",
                    phone1: "",
                    timezone: 'America/New_York',
                    type: 'Registry',
                    address1: null,
                    city: null,
                    state: null,
                    zip: null,
                    chain_id: "",
                    new_chain_name: "",
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
        },

        watch: {

        }
    }
</script>
