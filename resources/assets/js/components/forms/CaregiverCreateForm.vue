<template>
    <b-row>
        <b-col lg="6">
            <b-form-group label="First Name" label-for="firstname" label-class="required">
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
            <b-form-group label="Last Name" label-for="lastname" label-class="required">
                <b-form-input
                        id="lastname"
                        name="lastname"
                        type="text"
                        v-model.lazy="form.lastname"
                        required
                >
                </b-form-input>
                <input-help :form="form" field="lastname" text="Enter their last name."></input-help>
            </b-form-group>
            <b-form-group label="Date of Birth" label-for="date_of_birth">
                <mask-input v-model="form.date_of_birth" id="date_of_birth" type="date"></mask-input>
                <input-help :form="form" field="date_of_birth"
                            text="Enter their date of birth. Ex: MM/DD/YYYY"></input-help>
            </b-form-group>
            <b-form-group label="Social Security Number" label-for="ssn">
                <mask-input id="ssn" name="ssn" v-model="form.ssn" type="ssn"></mask-input>
                <input-help :form="form" field="ssn"
                            text="Enter their social security number or ein. Ex: 123-45-6789"></input-help>
            </b-form-group>
        </b-col>
        <b-col lg="6">
            <b-form-group label="Title" label-for="title" label-class="required">
                <b-form-input
                        id="title"
                        name="title"
                        type="text"
                        v-model="form.title"
                >
                </b-form-input>
                <input-help :form="form" field="title"
                            text="Enter the caregiver's title (example: CNA)"></input-help>
            </b-form-group>
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
                            text="Enter their email address or check the box if caregiver does not have an email."></input-help>
            </b-form-group>
            <b-form-group label="Username" label-for="username">
                <b-row>
                    <b-col cols="8">
                        <b-form-input
                                id="username"
                                name="username"
                                type="text"
                                v-model="form.username"
                                :disabled="form.no_username"
                        >
                        </b-form-input>
                    </b-col>
                    <b-col cols="4">
                        <div class="form-check">
                            <label class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" name="no_username"
                                       v-model="form.no_username" value="1" @input="toggleNoUsername()">
                                <span class="custom-control-indicator"></span>
                                <span class="custom-control-description">Let Caregiver Choose</span>
                            </label>
                        </div>
                    </b-col>
                </b-row>
                <input-help :form="form" field="username" text="Enter their username to be used for logins."></input-help>
            </b-form-group>
            <b-form-group label="Password" label-for="password">
                <b-form-input
                        id="password"
                        name="password"
                        type="password"
                        v-model="form.password"
                        :disabled="form.no_username"
                >
                </b-form-input>
                <input-help :form="form" field="password"
                            text="Enter the password they will use to login for the first time."></input-help>
            </b-form-group>
            <b-form-group label="Confirm Password" label-for="password_confirmation">
                <b-form-input
                        id="password_confirmation"
                        name="password_confirmation"
                        type="password"
                        v-model="form.password_confirmation"
                        :disabled="form.no_username"
                >
                </b-form-input>
                <input-help :form="form" field="password_confirmation"
                            text="Re-enter the above password."></input-help>
            </b-form-group>
        </b-col>
    </b-row>
</template>

<script>
    export default {

        props: {
            value: Object,
        },

        data() {
            return {
                form: new Form({
                    firstname: this.value.firstname || null,
                    lastname: this.value.lastname || null,
                    email: this.value.email || null,
                    no_email: !!this.value.no_email,
                    username: this.value.username || null,
                    no_username: !!this.value.no_username,
                    date_of_birth: this.value.date_of_birth || null,
                    ssn: this.value.ssn || null,
                    password: this.value.password || null,
                    password_confirmation: this.value.password_confirmation || null,
                    title: this.value.title || null,
                    override: false,
                    business_id: this.value.business_id || null
                }),
            }
        },

        mounted() {

        },

        methods: {
            copyEmailToUsername() {
                if (this.form.email && (this.form.no_username || !this.form.username)) {
                    this.form.username = this.form.email;
                }
            },

            toggleNoEmail() {
                if (this.form.no_email) {
                    this.form.email = '';
                }
            },

            toggleNoUsername() {
                if (this.form.no_username) {
                    this.form.username = this.form.email;
                    this.form.password = '';
                    this.form.password_confirmation = '';
                }
            },
        },

        watch: {
            form: {
                handler(obj){
                    this.$emit('input', obj);
                },
                deep: true
            }
        },

    }
</script>

<style scoped>

</style>