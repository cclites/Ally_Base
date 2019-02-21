<template>
    <b-card header="Edit Business Location"
            header-bg-variant="info"
            header-text-variant="white"
    >
        <form @submit.prevent="submitForm()" @keydown="form.clearError($event.target.name)">
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
                    <b-button id="save-business" variant="success" type="submit">Save Provider</b-button>
                </b-col>
            </b-row>
        </form>
    </b-card>
</template>

<script>
    export default {
        props: {
            business: {},
        },

        data() {
            return {
                form: new Form({
                    name: this.business.name,
                    short_name: this.business.short_name,
                    phone1: this.business.phone1,
                    timezone: this.business.timezone,
                    address1: this.business.address1,
                    city: this.business.city,
                    state: this.business.state,
                    zip: this.business.zip,
                    type: this.business.type
                })
            }
        },

        mounted() {
        },

        methods: {
            submitForm() {
                this.form.patch('/admin/businesses/' + this.business.id);
            }
        }
    }
</script>
