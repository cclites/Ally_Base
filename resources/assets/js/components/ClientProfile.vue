<template>
    <b-row>
        <b-col lg="12">
            <b-card header="Profile"
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
                                <input-help :form="form" field="firstname" text="Enter your first name."></input-help>
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
                                <input-help :form="form" field="lastname" text="Enter your last name."></input-help>
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
                                <input-help :form="form" field="email" text="Enter your email address.  Ex: user@domain.com"></input-help>
                            </b-form-group>
                            <b-form-group label="Date of Birth" label-for="date_of_birth">
                                <b-form-input
                                        id="date_of_birth"
                                        name="date_of_birth"
                                        type="text"
                                        v-model="form.date_of_birth"
                                >
                                </b-form-input>
                                <input-help :form="form" field="date_of_birth" text="Enter your date of birth. Ex: MM/DD/YYYY"></input-help>
                            </b-form-group>
                        </b-col>
                    </b-row>
                    <b-row>
                        <b-col lg="12">
                            <b-button id="save-profile" variant="success" type="submit">Save Profile</b-button>
                        </b-col>
                    </b-row>
                </form>
            </b-card>
        </b-col>
    </b-row>
</template>

<script>
    export default {
        props: {
            'client': {},
            'user': {}
        },

        data() {
            return {
                form: new Form({
                    firstname: this.user.firstname,
                    lastname: this.user.lastname,
                    email: this.user.email,
                    date_of_birth: moment(this.user.date_of_birth).format('L')
                })
            }
        },

        mounted() {

        },

        methods: {

            saveProfile() {
                this.form.post('/profile');
            }

        }


    }
</script>
