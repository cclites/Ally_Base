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
                        v-model="form.lastname"
                        required
                >
                </b-form-input>
                <input-help :form="form" field="lastname" text="Enter their last name."></input-help>
            </b-form-group>
            <b-form-group label="Client Type" label-for="client_type" label-class="required">
                <client-type-dropdown
                        id="client_type"
                        name="client_type"
                        v-model="form.client_type"
                />
                <input-help :form="form" field="client_type" text="Select the type of payment the client will use."></input-help>
            </b-form-group>
            <b-form-group>
                <div class="form-check">
                    <label class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input"
                               v-model="form.provider_pay" value="1">
                        <span class="custom-control-indicator"></span>
                        <span class="custom-control-description">Use Provider Pay</span>
                    </label>
                </div>
                <input-help :form="form" field="provider_pay" text="Set the payment method to the registry's bank account."></input-help>
            </b-form-group>
            <business-location-form-group v-model="form.business_id"
                                          :form="form"
                                          field="business_id"
                                          help-text="Select the office location for the client.">
            </business-location-form-group>
            <b-form-group label="Status">
                <b-form-select name="status_alias_id" v-model="form.status_alias_id">
                    <option value="0">Active</option>
                    <option value="-1">Inactive</option>
                    <option v-for="item in statusAliases" :key="item.id" :value="item.id">
                        {{ item.name }} ({{ item.active ? 'Active' : 'Inactive' }})
                    </option>
                </b-form-select>
                <input-help :form="form" field="status_alias_id" text=""></input-help>
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
                                       v-model="form.no_email" value="1" @input="toggleNoEmail()">
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
                                <span class="custom-control-description">Let Client Choose</span>
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
            <b-form-group label="Date of Birth" label-for="date_of_birth">
                <mask-input v-model="form.date_of_birth" id="date_of_birth" type="date"></mask-input>
                <input-help :form="form" field="date_of_birth" text="Enter their date of birth. Ex: MM/DD/YYYY"></input-help>
            </b-form-group>
            <b-form-group label="Social Security Number" label-for="ssn">
                <mask-input v-model="form.ssn" id="ssn" name="ssn" type="ssn"></mask-input>
                <input-help :form="form" field="ssn" text="Enter the client's social security number."></input-help>
            </b-form-group>
            <b-form-group label="Ally Client Agreement Status" label-for="agreement_status" label-class="required">
                <b-form-select
                        id="agreement_status"
                        name="agreement_status"
                        v-model="form.agreement_status"
                >
                    <option value="">--Select--</option>
                    <option v-for="(display, value) in onboardStatuses" :value="value" :key="value">{{ display }}</option>
                </b-form-select>
                <input-help :form="form" field="agreement_status" text="Select the Ally Agreement status of the client"></input-help>
            </b-form-group>
        </b-col>
    </b-row>
</template>

<script>
    import ClientForm from '../../mixins/ClientForm';
    import BusinessLocationFormGroup from "../business/BusinessLocationFormGroup";
    import Constants from '../../mixins/Constants';

    export default {
        components: {BusinessLocationFormGroup},
        mixins: [ClientForm, Constants],

        props: {
            value: Object,
        },

        data() {
            return {
                statusAliases: [],
                form: new Form({
                    firstname: this.value.firstname || null,
                    lastname: this.value.lastname || null,
                    email: this.value.email || null,
                    no_email: !!this.value.no_email,
                    username: this.value.username || null,
                    no_username: !!this.value.no_username,
                    date_of_birth: this.value.date_of_birth || null,
                    client_type: this.value.client_type || "",
                    ssn: this.value.ssn || null,
                    agreement_status: this.value.agreement_status || "",
                    override: false,
                    provider_pay: 0,
                    business_id: this.value.business_id || "",
                    password: this.value.password || null,
                    password_confirmation: this.value.password_confirmation || null,
                    status_alias_id: "0",
                }),
            }
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

            async fetchStatusAliases() {
                this.statusAliases = [];
                axios.get(`/business/status-aliases?business_id=${this.form.business_id}`)
                    .then( ({ data }) => {
                        this.statusAliases = data.client;
                    })
                    .catch(() => {});
            },
        },

        watch: {
            'form.business_id'(newVal, oldVal) {
                this.fetchStatusAliases();
            },

            form: {
                handler(obj){
                    this.$emit('input', obj);
                },
                deep: true
            },

            value: {
                handler(obj){
                    for (let key in obj.data()) {
                        if (this.form[key] !== obj[key]) {
                            this.form[key] = obj[key];
                        }
                    }
                },
                deep: true
            },
        },

        async mounted() {
            await this.fetchStatusAliases();
        }
    }
</script>

<style scoped>

</style>
