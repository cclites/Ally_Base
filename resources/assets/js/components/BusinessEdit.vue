<template>
    <b-card header="Edit Provider"
            header-bg-variant="info"
            header-text-variant="white"
    >
        <form @submit.prevent="submitForm()" @keydown="form.clearError($event.target.name)">
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
                    <b-form-group label="Outgoing SMS Number" label-for="outgoing_sms_number">
                        <b-form-input
                                id="outgoing_sms_number"
                                name="outgoing_sms_number"
                                type="text"
                                v-model="form.outgoing_sms_number"
                                required
                        >
                        </b-form-input>
                        <input-help :form="form" field="outgoing_sms_number" text="The number used to dispatch text messages."></input-help>
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
                    phone1: this.business.phone1,
                    timezone: this.business.timezone,
                    address1: this.business.address1,
                    city: this.business.city,
                    state: this.business.state,
                    zip: this.business.zip,
                    outgoing_sms_number: this.business.outgoing_sms_number,
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
