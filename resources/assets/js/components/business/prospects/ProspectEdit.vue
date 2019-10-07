<template>
    <b-card :header="title"
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
                                required
                                empty-text="-- Select Client Type --"
                        />
                        <input-help :form="form" field="client_type" text="Select the type of payment the prospect will use."></input-help>
                    </b-form-group>
                    <business-location-form-group v-model="form.business_id"
                                                  :form="form"
                                                  field="business_id"
                                                  help-text="Select the office location for the prospect.">
                    </business-location-form-group>
                </b-col>
                <b-col lg="6">
                    <b-form-group label="Email Address" label-for="email">
                        <b-form-input
                                id="email"
                                name="email"
                                type="email"
                                v-model="form.email"
                                :disabled="form.no_email"
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
                    <business-referral-source-select v-model="form.referral_source_id" source-type="client"></business-referral-source-select>
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
            <h4>Prospect Status</h4>
            <b-row>
                <b-col lg="12">
                    <div class="form-check form-inline">
                        <label class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input"
                                   v-model="form.had_initial_call" :true-value="1" :false-value="0">
                            <span class="custom-control-indicator"></span>
                            <span class="custom-control-description"> Initial Phone Call</span>
                        </label>
                        <date-picker v-model="form.initial_call_date"></date-picker>
                    </div>
                </b-col>
                <b-col lg="4" v-for="(status,text) in statuses" :key="status">
                    <label class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input"
                               v-model="form[status]" :true-value="1" :false-value="0">
                        <span class="custom-control-indicator"></span>
                        <span class="custom-control-description">{{ text }}</span>
                    </label>
                </b-col>
            </b-row>
            <b-row>
                <b-col lg="12">
                    <submit-button variant="success"
                                   type="submit"
                                   :submitting="submitting"
                    >
                        Save
                    </submit-button>
                    <b-btn @click="convert(prospect)" v-if="prospect">Convert to Client</b-btn>
                    <b-btn @click="destroy(prospect)" v-if="prospect" variant="danger">Delete Prospect</b-btn>
                </b-col>
            </b-row>
        </form>
    </b-card>
</template>

<script>
    import Countries from "../../../classes/Countries";
    import States from "../../../classes/States";
    import BusinessLocationFormGroup from "../BusinessLocationFormGroup";
    import Constants from '../../../mixins/Constants';

    export default {
        components: {BusinessLocationFormGroup},
        mixins: [Constants],

        props: ['prospect', 'referralsources'],

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
                return this.prospect ? 'Edit Prospect' : 'New Prospect';
            }
        },

        mounted() {
        },

        methods: {

            getOriginal(field, defaultValue = "") {
                return this.prospect ? this.prospect[field] : defaultValue;
            },

            getOriginalDate(field, defaultValue = "") {
                return this.prospect && this.prospect[field] ? moment(this.prospect[field]).format('MM/DD/YYYY') : defaultValue;
            },

            async saveProfile() {
                this.submitting = true;
                try {
                    let response;
                    if (this.prospect) {
                        response = await this.form.patch(`/business/prospects/${this.prospect.id}`);
                    }
                    else {
                        response = await this.form.post('/business/prospects');
                    }
                }
                catch(error) {}
                this.submitting = false;
            },

            convert(item) {
                if (!confirm(`Are you sure you wish to convert ${item.firstname} ${item.lastname} to a client?`)) return;
                let form = new Form({});
                form.post(`/business/prospects/${item.id}/convert`);
            },

            destroy(item) {
                if (!confirm(`Are you sure you wish to delete ${item.firstname} ${item.lastname}?`)) return;
                let form = new Form({});
                form.submit('delete', `/business/prospects/${item.id}`);
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