<template>
    <b-card header="New Caregiver"
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
                    <b-form-group label="Date of Birth" label-for="date_of_birth">
                        <b-form-input
                                id="date_of_birth"
                                name="date_of_birth"
                                type="text"
                                v-model="form.date_of_birth"
                        >
                        </b-form-input>
                        <input-help :form="form" field="date_of_birth" text="Enter their date of birth. Ex: MM/DD/YYYY"></input-help>
                    </b-form-group>
                    <b-form-group label="Social Security Number" label-for="ssn">
                        <b-form-input
                                id="ssn"
                                name="ssn"
                                type="text"
                                v-model="form.ssn"
                        >
                        </b-form-input>
                        <input-help :form="form" field="date_of_birth" text="Enter their social security number or ein. Ex: 123-45-6789"></input-help>
                    </b-form-group>
                </b-col>
                <b-col lg="6">
                    <b-form-group label="Email Address" label-for="email">
                        <b-form-input
                            id="email"
                            name="email"
                            type="email"
                            v-model="form.email"
                            >
                        </b-form-input>
                        <input-help :form="form" field="email" text="Enter their email address.  Ex: user@domain.com"></input-help>
                    </b-form-group>
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
                    <b-button id="save-profile" variant="success" type="submit">Create &amp; Continue</b-button>
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
                    firstname: null,
                    lastname: null,
                    email: null,
                    date_of_birth: null,
                    ssn: null,
                    password: null,
                    password_confirmation: null,
                })
            }
        },

        mounted() {
        },

        methods: {

            saveProfile() {
                this.form.post('/business/caregivers');
            }

        }


    }
</script>
