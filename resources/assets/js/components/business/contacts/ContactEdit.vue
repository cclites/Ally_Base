<template>
    <b-card :header="title"
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
                        <input-help :form="form" field="client_type" text="Select the type of payment the prospect will use."></input-help>
                    </b-form-group>
                    <b-form-group label="Office Location" label-for="business_id">
                        <business-location-select v-model="form.business_id"></business-location-select>
                        <input-help :form="form" field="business_id" text="Select the type of payment the prospect will use."></input-help>
                    </b-form-group>
                </b-col>
                <b-col lg="6">
                    <b-form-group label="Email Address" label-for="email">
                        <b-form-input
                                id="email"
                                name="email"
                                type="email"
                                v-model="form.email"
                                :disabled="form.no_email"
                                @change="copyEmailToUsername()"
                        >
                        </b-form-input>
                        <input-help :form="form" field="email"
                                    text="Enter their email address. Ex: user@domain.com"></input-help>
                    </b-form-group>
                    <b-form-group label="Date of Birth" label-for="date_of_birth">
                        <mask-input v-model="form.date_of_birth" id="date_of_birth" type="date"></mask-input>
                        <input-help :form="form" field="date_of_birth" text="Enter their date of birth. Ex: MM/DD/YYYY"></input-help>
                    </b-form-group>
                    <b-form-group label="Phone Number" label-for="phone">
                        <mask-input v-model="form.phone" name="phone"></mask-input>
                        <input-help :form="form" field="phone" text="Enter full phone number."></input-help>
                    </b-form-group>
                </b-col>
            </b-row>
            <hr />
            <b-row>
                <b-col lg="6">
                    <b-form-group label="Address Line 1" label-for="address1">
                        <b-form-input
                                id="address1"
                                name="address1"
                                type="text"
                                v-model="form.address1"
                        >
                        </b-form-input>
                        <input-help :form="form" field="address1" text="Enter your street number and name here."></input-help>
                    </b-form-group>
                    <b-form-group label="Address Line 2" label-for="address2">
                        <b-form-input
                                id="address2"
                                name="address2"
                                type="text"
                                v-model="form.address2"
                        >
                        </b-form-input>
                        <input-help :form="form" field="address2" text="Enter an apartment number or additional address info here. (Optional)"></input-help>
                    </b-form-group>
                    <b-form-group label="City" label-for="city">
                        <b-form-input
                                id="city"
                                name="city"
                                type="text"
                                v-model="form.city"
                        >
                        </b-form-input>
                        <input-help :form="form" field="city" text="Enter the city here."></input-help>
                    </b-form-group>
                </b-col>
                <b-col lg="6">
                    <b-form-group label="State" label-for="state">
                        <b-form-select name="state" id="state" :options="states.getOptions()" v-model="form.state">
                        </b-form-select>
                        <input-help :form="form" field="state" text="Select the state from the drop down."></input-help>
                    </b-form-group>
                    <b-form-group label="Zip Code" label-for="zip">
                        <b-form-input
                                id="zip"
                                name="zip"
                                type="text"
                                v-model="form.zip"
                        >
                        </b-form-input>
                        <input-help :form="form" field="zip" text="Enter the zip code or postal code here."></input-help>
                    </b-form-group>
                    <b-form-group label="Country" label-for="country">
                        <b-form-select name="country" id="country" :options="countries.getOptions()" v-model="form.country">
                        </b-form-select>
                        <input-help :form="form" field="country" text="Select the country from the drop down."></input-help>
                    </b-form-group>
                </b-col>
            </b-row>
            <hr />
            <b-row>
                <b-col lg="6">
                    <referral-source-select v-model="form.referral_source_id" :business-id="form.business_id"></referral-source-select>
                    <input-help :form="form" field="referred_by" text="Enter how the prospect was referred." />
                </b-col>
                <b-col lg="6">
                    <b-form-group label="Last Contacted" label-for="last_contacted">
                        <date-picker v-model="form.last_contacted"></date-picker>
                        <input-help :form="form" field="last_contacted" text="Enter when the prospect was contacted." />
                    </b-form-group>
                </b-col>
            </b-row>
            <hr />
            <b-row>
                <b-col lg="12">
                    <submit-button variant="success"
                                   type="submit"
                                   :submitting="submitting"
                    >
                        Save
                    </submit-button>
                    <b-btn @click="destroy(contact)" v-if="contact" variant="danger">Delete Contact</b-btn>
                </b-col>
            </b-row>
        </form>
    </b-card>
</template>

<script>
    import Countries from "../../../classes/Countries";
    import States from "../../../classes/States";
    import ReferralSourceSelect from "../referral/ReferralSourceSelect";
    import BusinessLocationSelect from "../BusinessLocationSelect";

    export default {
        components: {BusinessLocationSelect, ReferralSourceSelect},
        props: ['contact', 'referralsources'],

        data() {
            return {
                form: new Form({
                    'firstname': this.getOriginal('firstname'),
                    'lastname': this.getOriginal('lastname'),
                    'email': this.getOriginal('email'),
                    'client_type': this.getOriginal('client_type'),
                    'business_id': this.getOriginal('business_id'),
                    'date_of_birth': this.getOriginalDate('date_of_birth'),
                    'phone': this.getOriginal('phone'),
                    'address1': this.getOriginal('address1'),
                    'address2': this.getOriginal('address2'),
                    'city': this.getOriginal('city'),
                    'state': this.getOriginal('state'),
                    'zip': this.getOriginal('zip'),
                    'country': this.getOriginal('country', 'US'),
                    'referral_source_id': this.getOriginal('referral_source_id'),
                    'last_contacted': this.getOriginalDate('last_contacted'),
                    'initial_call_date': this.getOriginalDate('initial_call_date'),
                    'had_initial_call': this.getOriginal('had_initial_call', 0),
                    'had_assessment_scheduled': this.getOriginal('had_assessment_scheduled', 0),
                    'had_assessment_performed': this.getOriginal('had_assessment_performed', 0),
                    'needs_contract': this.getOriginal('needs_contract', 0),
                    'expecting_client_signature': this.getOriginal('expecting_client_signature', 0),
                    'needs_payment_info': this.getOriginal('needs_payment_info', 0),
                    'ready_to_schedule': this.getOriginal('ready_to_schedule', 0),
                    'closed_loss': this.getOriginal('closed_loss', 0),
                    'closed_win': this.getOriginal('closed_win', 0),
                }),
                statuses: {
                    'Assessment Scheduled': 'had_assessment_scheduled',
                    'Assessment Performed': 'had_assessment_performed',
                    'Needs Contract': 'needs_contract',
                    'Expecting Client Signature': 'expecting_client_signature',
                    'Collect Payment Information': 'needs_payment_info',
                    'Ready to Schedule Care': 'ready_to_schedule',
                    'Closed - Loss': 'closed_loss',
                    'Closed - Win': 'closed_win',
                },
                submitting: false,
                countries: new Countries(),
                states: new States(),
            }
        },

        computed: {
            title() {
                return this.contact ? 'Edit Contact' : 'New Contact';
            }
        },

        mounted() {
        },

        methods: {

            getOriginal(field, defaultValue = "") {
                return this.contact ? this.contact[field] : defaultValue;
            },

            getOriginalDate(field, defaultValue = "") {
                return this.contact && this.contact[field] ? moment(this.contact[field]).format('MM/DD/YYYY') : defaultValue;
            },

            async saveProfile() {
                this.submitting = true;
                try {
                    let response;
                    if (this.contact) {
                        response = await this.form.patch(`/business/contacts/${this.contact.id}`);
                    }
                    else {
                        response = await this.form.post('/business/contacts');
                    }
                }
                catch(error) {}
                this.submitting = false;
            },

            destroy(item) {
                if (!confirm(`Are you sure you wish to delete ${item.firstname} ${item.lastname}?`)) return;
                let form = new Form({});
                form.submit('delete', `/business/contacts/${item.id}`);
            },

            closemodal(status) {
                this.show = status;
            }
        },



    }
</script>

<style scoped>
    .pad-top {
        padding-top: 16px;
    }
</style>