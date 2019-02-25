<template>
    <div>
        <loading-card v-if="loading" text="Loading account information..."></loading-card>

        <form v-else @submit.prevent="submit()" @keydown="form.clearError($event.target.name)" autocomplete="off">
            <b-row>
                <b-col cols="12" md="6" lg="8">
        <b-button @click="dummyData()">Dummy Data</b-button>
                    <b-card
                            header="Direct Deposit Bank Account"
                            header-text-variant="white"
                            header-bg-variant="info">

                        <bank-account-form 
                            source="primary"
                            :submit-url="null"
                            :readonly="false"
                            ref="bankAccountForm"
                        />
                    </b-card>
                    <!-- W9 Form -->
                    <b-card header="W-9 Request for Taxpayer Identification Number and Certification"
                            header-text-variant="white"
                            header-bg-variant="info">
                        <b-row>
                            <b-col>
                                <b-form-group>
                                    <b-button @click="fillW9Fields">Fill with profile information</b-button>
                                </b-form-group>
                                <b-form-group label="1. Name (as shown on your tax return)">
                                    <b-form-input v-model="form.w9.name"></b-form-input>
                                </b-form-group>
                                <b-form-group label="2. Business Name/disregarded entity name, if different from above">
                                    <b-form-input v-model="form.w9.business_name"></b-form-input>
                                </b-form-group>
                                <b-form-group label="3. Check appropriate box for federal tax classification of the person whose name is entered on line 1.">
                                    <b-form-radio-group v-model="form.w9.tax_classification"
                                                        stacked
                                                        :options="taxClassifications">
                                    </b-form-radio-group>
                                </b-form-group>
                                <b-form-group v-if="form.w9.tax_classification === 'llc'" label="Enter the tax classification">
                                    <b-form-radio-group v-model="form.w9.llc_type">
                                        <b-form-radio value="C">C Corporation</b-form-radio>
                                        <b-form-radio value="S">S Corporation</b-form-radio>
                                        <b-form-radio value="P">Partnership</b-form-radio>
                                    </b-form-radio-group>
                                </b-form-group>
                                <b-form-group v-if="form.w9.tax_classification === 'other'">
                                    <a href="https://www.irs.gov/pub/irs-pdf/fw9.pdf" target="_blank">See Instructions</a>
                                    <b-form-textarea :rows="3"></b-form-textarea>
                                </b-form-group>
                                <b-form-group label="4. Exemptions (codes apply only to certain entities, not individuals)">
                                    <b-row>
                                    <b-form-group label="Exempt payee code (if any)" class="col-md-6">
                                        <b-form-input v-model="form.w9.exempt_payee_code"></b-form-input>
                                    </b-form-group>
                                    <b-form-group label="Exemption from FATCA reporting code (if any)" class="col-md-6">
                                        <b-form-input v-model="form.w9.exempt_fatca_reporting_code"></b-form-input>
                                    </b-form-group>
                                    </b-row>
                                </b-form-group>
                                <b-form-group label="5. Address (number, street, and apt. or suite no.)">
                                    <b-form-input v-model="form.w9.address"></b-form-input>
                                </b-form-group>
                                <b-form-group label="6. City, state, and ZIP code">
                                    <b-form-input v-model="form.w9.city_state_zip"></b-form-input>
                                </b-form-group>
                                <b-form-group label="7. List account number(s) here (optional)">
                                    <b-form-input v-model="form.w9.account_numbers"></b-form-input>
                                </b-form-group>
                                <b-form-group label="Taxpayer Identification Number (TIN)">
                                    <b-row>
                                        <b-form-group label="Social security number" class="col-md-5">
                                            <b-form-input v-model="form.w9.ssn"></b-form-input>
                                        </b-form-group>
                                        <div class="col-md-2 text-center">
                                            <b-form-group label="&nbsp;">
                                                <div class="mt-1">OR</div>
                                            </b-form-group>
                                        </div>
                                        <b-form-group label="Employer identification number" class="col-md-5">
                                            <b-form-input v-model="form.w9.employer_id_number"></b-form-input>
                                        </b-form-group>
                                    </b-row>
                                </b-form-group>
                            </b-col>
                        </b-row>
                    </b-card>
                    <!-- End W9 Form-->
                </b-col>
                <b-col cols="12" md="6" lg="4">
                    <b-card
                        header="Example Check"
                        header-text-variant="white"
                        header-bg-variant="info"
                        img-src="https://www.howardbank.com/wp-content/uploads/how-to-void-a-check.gif"
                        img-alt="Voided Check"
                        img-bottom>
                    </b-card>
                    <b-card
                        header="Direct Deposit Terms"
                        header-text-variant="white"
                        header-bg-variant="info">
                        <p class="card-text">I hereby authorize Ally, LLC (Ally) to debit entries, and if necessary credit entries to correct erroneous debits from the account at the Financial Institution named above.  If a debit is scheduled to take place on a non-banking date, the transaction will take place on the next banking day. I (we) acknowledge the origination of ACH transactions to our account must comply with the provisions of U.S. law.  This authority is to remain in full force and effect until Ally has received written notification from me of termination in such time and manner as to afford Ally and Financial Institution a reasonable opportunity to act on it.</p>
                    </b-card>
                </b-col>
            </b-row>
            <b-row>
                <b-col lg="12" class="text-right">
                    <div class="form-check">
                        <label class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" name="accepted_terms" v-model="form.accepted_terms" value="1" :disabled="busy">
                            <span class="custom-control-indicator"></span>
                            <span class="custom-control-description"><b>I accept the direct deposit terms above</b></span>
                        </label>
                        <input-help :form="form" field="accepted_terms" text=""></input-help>
                    </div>
                    <b-button variant="success" size="lg" type="submit" :disabled="busy">
                        <i v-if="busy" class="fa fa-spinner fa-spin mr-2" size="lg"></i>
                        Accept and Finish Account Setup
                    </b-button>
                </b-col>
            </b-row>
        </form>
    </div>
</template>

<script>
    export default {
        props: {
            'token': {},
            'caregiver': {},
        },

        data() {
            return {
                busy: false,
                loading: false,
                form: new Form({
                    accepted_terms: 0,
                    w9: {
                        name: '',
                        business_name: '',
                        tax_classification: '',
                        llc_type: '',
                        exempt_payee_code: '',
                        exempt_fatca_reporting_code: '',
                        address: '',
                        city_state_zip: '',
                        account_numbers: '',
                        ssn: '',
                        employer_id_number: ''
                    }
                }),
                taxClassifications: [
                    {
                        text: 'Individual/sole proprietor or single-member LLC',
                        value: 'individual_sole_prop'
                    },
                    {
                        text: 'C Corporation',
                        value: 'c_corp'
                    },
                    {
                        text: 'S Corporation',
                        value: 's_corp'
                    },
                    {
                        text: 'Partnership',
                        value: 'partnership'
                    },
                    {
                        text: 'Trust/Estate',
                        value: 'trust_estate'
                    },
                    {
                        text: 'Limited liability company.',
                        value: 'llc'
                    },
                    {
                        text: 'Other',
                        value: 'other'
                    }
                ],
            }
        },

        async mounted() {
            this.loading = true;
            // check if step should be skipped
            axios.get(`/account-setup/caregivers/${this.token}/check`)
                .then( ({ data }) => {
                    this.$emit('updated', data);
                })
                .catch(e => {
                })
                .finally(() => {
                    this.loading = false;
                });
        },

        computed: {
            submitUrl() {
                return ''; 
            },

            w9Address() {
                let address = this.caregiver.address.address1;
                if (this.caregiver.address.address2) {
                    address += ' ' + this.caregiver.address.address2;
                }
                return address;
            },

            w9CityStateZip() {
                let address = '';
                if (this.caregiver.address.city) {
                    address += ' ' + this.caregiver.address.city;
                }
                if (this.caregiver.address.state) {
                    address += ' ' + this.caregiver.address.state;
                }
                if (this.caregiver.address.zip) {
                    address += ' ' + this.caregiver.address.zip;
                }
                return address;
            },
        },

        methods: {
            dummyData() {
                this.$refs.bankAccountForm.form.nickname = 'test acocunt';
                this.$refs.bankAccountForm.form.name_on_account = 'john doe';
                this.$refs.bankAccountForm.form.routing_number = '123456789';
                this.$refs.bankAccountForm.form.routing_number_confirmation = '123456789';
                this.$refs.bankAccountForm.form.account_number = '123456789';
                this.$refs.bankAccountForm.form.account_number_confirmation = '123456789';
                this.$refs.bankAccountForm.form.account_type = 'checking';
                this.$refs.bankAccountForm.form.account_holder_type = 'personal';
                this.$refs.bankAccountForm.form.ignore_validation = true;
            },

            submit() {
                this.form.combineForm(this.$refs.bankAccountForm.form);
                
                this.busy = true;
                this.form.post(`/account-setup/caregivers/${this.token}/step3`)
                    .then( ({ data }) => {
                        this.$emit('updated', data.data);
                    })
                    .catch(e => {
                    })
                    .finally(() => {
                        this.busy = false;
                    });
            },

            fillW9Fields() {
                this.form.w9.name = this.caregiver.firstname + ' ' + this.caregiver.lastname;
                this.form.w9.address = this.w9Address;
                this.form.w9.city_state_zip = this.w9CityStateZip;
                this.form.w9.ssn = this.caregiver.ssn;
            },

        }
    }
</script>
