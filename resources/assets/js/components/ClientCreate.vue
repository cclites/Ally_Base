<template>
    <b-card header="New Client"
        header-bg-variant="info"
        header-text-variant="white"
        >
        <form @submit.prevent="saveProfile()" @keydown="form.clearError($event.target.name)">
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
                    <b-form-group label="Client Type" label-for="client_type">
                        <b-form-select
                                id="client_type"
                                name="client_type"
                                v-model="form.client_type"
                        >
                            <option value="">--Select--</option>
                            <option value="private_pay">Private Pay</option>
                            <option value="medicaid">Medicaid</option>
                            <option value="VA">VA</option>
                            <option value="LTCI">LTC Insurance</option>
                        </b-form-select>
                        <input-help :form="form" field="client_type" text="Select the type of payment the client will use."></input-help>
                    </b-form-group>
                    <b-form-group label="Ally Onboard Status" label-for="onboard_status">
                        <b-form-select
                                id="onboard_status"
                                name="onboard_status"
                                v-model="form.onboard_status"
                        >
                            <option value="">--Select--</option>
                            <option v-for="(display, value) in onboardStatuses" :value="value">{{ display }}</option>
                        </b-form-select>
                        <input-help :form="form" field="onboard_status" text="Select the Ally Agreement status of the client"></input-help>
                    </b-form-group>
                </b-col>
                <b-col lg="6">
                    <b-form-group label="Email Address" label-for="email">
                        <b-row>
                            <b-col cols="8">
                                <b-form-input
                                        id="email"
                                        name="email"
                                        type="email"
                                        v-model="form.email"
                                        :disabled="form.no_email"
                                        @change="copyEmailToUsername()"
                                >
                                </b-form-input>
                            </b-col>
                            <b-col cols="4">
                                <div class="form-check">
                                    <label class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" name="no_email"
                                               v-model="form.no_email" value="1">
                                        <span class="custom-control-indicator"></span>
                                        <span class="custom-control-description">No Email</span>
                                    </label>
                                </div>
                            </b-col>
                        </b-row>
                        <input-help :form="form" field="email"
                                    text="Enter their email address or check the box if client does not have an email. Ex: user@domain.com"></input-help>
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
                    <b-form-group label="Date of Birth" label-for="date_of_birth">
                        <mask-input v-model="form.date_of_birth" id="date_of_birth" type="date"></mask-input>
                        <input-help :form="form" field="date_of_birth" text="Enter their date of birth. Ex: MM/DD/YYYY"></input-help>
                    </b-form-group>
                    <b-form-group label="Social Security Number" label-for="ssn">
                        <mask-input v-model="form.ssn" id="ssn" name="ssn" type="ssn"></mask-input>
                        <input-help :form="form" field="ssn" text="Enter the client's social security number."></input-help>
                    </b-form-group>
                </b-col>
            </b-row>
            <b-row>
                <b-col lg="12">
                    <b-button id="save-profile" variant="success" type="submit">Create &amp; Continue</b-button>
                </b-col>
            </b-row>
        </form>
    </b-card>
</template>

<script>
    import ClientForm from '../mixins/ClientForm';

    export default {
        props: {},

        data() {
            return {
                form: new Form({
                    firstname: null,
                    lastname: null,
                    email: null,
                    no_email: null,
                    username: null,
                    date_of_birth: null,
                    client_type: '',
                    ssn: '',
                    onboard_status: '',
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

            saveProfile() {
                this.form.post('/business/clients');
            }

        },

        mixins: [ClientForm],


    }
</script>
