<template>
    <b-card header="Profile"
        header-bg-variant="info"
        header-text-variant="white"
        >
        <form @submit.prevent="saveProfile()" @keydown="form.clearError($event.target.name)">
            <b-row>
                <b-col lg="6">
                    <b-form-group label="First Name" label-for="firstname" label-class="required">
                        <b-form-input
                            id="firstname"
                            name="firstname"
                            type="text"
                            v-model="form.firstname"
                            required
                            :readonly="authInactive"
                        >
                        </b-form-input>
                        <input-help :form="form" field="firstname" text="Enter your first name."></input-help>
                    </b-form-group>
                    <b-form-group label="Last Name" label-for="lastname" label-class="required">
                        <b-form-input
                            id="lastname"
                            name="lastname"
                            type="text"
                            v-model="form.lastname"
                            required
                            :readonly="authInactive"
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
                            :readonly="authInactive"
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
                                :readonly="authInactive"
                        >
                        </b-form-input>
                        <input-help :form="form" field="date_of_birth" text="Enter your date of birth. Ex: MM/DD/YYYY"></input-help>
                    </b-form-group>
                </b-col>
            </b-row>
            <template v-if="user.role_type === 'client'">
                <b-row>
                    <b-col>
                        <p class="h6">Power of Attorney</p>
                        <hr>
                    </b-col>
                </b-row>
                <b-row>
                    <b-col lg="6">
                        <b-form-group label="First Name">
                            <b-form-input id="poa_first_name"
                                          v-model="form.poa_first_name"
                                          :readonly="authInactive"></b-form-input>
                        </b-form-group>
                        <b-form-group label="Phone">
                            <b-form-input id="poa_phone"
                                          v-model="form.poa_phone"
                                          :readonly="authInactive"></b-form-input>
                        </b-form-group>
                    </b-col>
                    <b-col lg="6">
                        <b-form-group label="Last Name">
                            <b-form-input id="poa_last_name"
                                          v-model="form.poa_last_name"
                                          :readonly="authInactive"></b-form-input>
                        </b-form-group>
                        <b-form-group label="Relationship">
                            <b-form-input id="poa_relationship"
                                          v-model="form.poa_relationship"
                                          :readonly="authInactive"></b-form-input>
                        </b-form-group>
                    </b-col>
                    <b-col lg="12">
                        <div class="form-check">
                            <label class="custom-control custom-checkbox">
                                <input type="checkbox"
                                    class="custom-control-input"
                                    name="caregiver_1099"
                                    v-model="form.caregiver_1099"
                                    :true-value="1"
                                    :false-value="0">
                                <span class="custom-control-indicator"></span>
                                <span class="custom-control-description">Send 1099 to caregivers on the client’s behalf</span>
                            </label>
                            <input-help :form="form" field="caregiver_1099" text=""></input-help>
                        </div>
                    </b-col>
                    <b-col lg="12">
                        <div class="form-check">
                            <label class="custom-control custom-checkbox">
                                <input type="checkbox"
                                    class="custom-control-input"
                                    name="receive_summary_email"
                                    v-model="form.receive_summary_email"
                                    :true-value="1"
                                    :false-value="0"
                                    :disabled="authInactive">
                                <span class="custom-control-indicator"></span>
                                <span class="custom-control-description">Receive the weekly shift summary email</span>
                            </label>
                            <input-help :form="form" field="receive_summary_email" text=""></input-help>
                        </div>
                    </b-col>
                </b-row>
            </template>
            <b-row>
                <b-col lg="12">
                    <b-button variant="success" type="submit" :disabled="authInactive">Save Profile</b-button>
                </b-col>
            </b-row>
        </form>
    </b-card>
</template>

<script>
    import FormatsDates from '../mixins/FormatsDates';
    import AuthUser from '../mixins/AuthUser';

    export default {
        props: {
            'client': {},
            'user': {}
        },

        mixins: [FormatsDates, AuthUser],

        data() {
            return {
                form: new Form({
                    firstname: this.user.firstname,
                    lastname: this.user.lastname,
                    email: this.user.email,
                    date_of_birth: (this.user.date_of_birth) ? this.formatDate(this.user.date_of_birth) : '',
                    poa_first_name: this.client.poa_first_name,
                    poa_last_name: this.client.poa_last_name,
                    poa_phone: this.client.poa_phone,
                    poa_relationship: this.client.poa_relationship,
                    receive_summary_email: this.client.receive_summary_email,
                    caregiver_1099: this.client.caregiver_1099
                })
            }
        },

        methods: {
            saveProfile() {
                this.form.post('/profile');
            }
        }
    }
</script>
