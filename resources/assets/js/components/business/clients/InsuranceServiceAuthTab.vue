<template>
    <div>
        <b-card header="Insurance Data"
                header-text-variant="white"
                header-bg-variant="info">
            <b-row>
                <b-col lg="6">
                    <b-form-group label="Company Name">
                        <b-form-input v-model="form.ltci_name"></b-form-input>
                    </b-form-group>

                    <b-form-group label="Company Phone">
                        <b-form-input v-model="form.ltci_phone"></b-form-input>
                    </b-form-group>

                    <b-form-group label="Company Fax">
                        <b-form-input v-model="form.ltci_fax"></b-form-input>
                    </b-form-group>
                </b-col>
                <b-col lg="6">
                    <b-form-group label="Company Address">
                        <b-form-input v-model="form.ltci_address"></b-form-input>
                    </b-form-group>

                    <b-form-group label="Company City">
                        <b-form-input v-model="form.ltci_city"></b-form-input>
                    </b-form-group>

                    <b-form-group label="Company State">
                        <b-form-input v-model="form.ltci_state"></b-form-input>
                    </b-form-group>

                    <b-form-group label="Company Zip">
                        <b-form-input v-model="form.ltci_zip"></b-form-input>
                    </b-form-group>
                </b-col>
            </b-row>
            <hr>
            <b-row>
                <b-col lg="6">
                    <b-form-group label="Policy #">
                        <b-form-input v-model="form.ltci_policy"></b-form-input>
                    </b-form-group>
                </b-col>
                <b-col lg="6">
                    <b-form-group label="Claim #">
                        <b-form-input v-model="form.ltci_claim"></b-form-input>
                    </b-form-group>
                </b-col>
            </b-row>
            <b-row>
                <b-col lg="12" class="pb-2">
                    <small>
                        * Ally does not guarantee insurance claim submission in any way.  It is the responsibility of the client to follow up with the insurance company on receipt and payment of claims.  Please complete and return the Ally Request to Submit Insurance Invoices form in order for Ally to submit on the client's behalf.
                    </small>
                </b-col>
                <b-col lg="12">
                    <b-btn variant="success" @click="updateInsuranceInfo()">Save Changes</b-btn>
                </b-col>
            </b-row>
        </b-card>
        <b-card header="Medicaid Data"
                header-text-variant="white"
                header-bg-variant="info">
            <b-row>
                <b-col lg="6">
                    <b-form-group label="Medicaid ID">
                        <b-form-input v-model="form.medicaid_id"></b-form-input>
                    </b-form-group>
                </b-col>
                <b-col lg="6">
                    <b-form-group label="Medicaid Diagnosis Code #1">
                        <b-form-input v-model="diagnosis_codes[0]"></b-form-input>
                    </b-form-group>
                </b-col>
                <b-col lg="6">
                    <b-form-group label="Medicaid Diagnosis Code #2">
                        <b-form-input v-model="diagnosis_codes[1]"></b-form-input>
                    </b-form-group>
                </b-col>
                <b-col lg="6">
                    <b-form-group label="Medicaid Diagnosis Code #3">
                        <b-form-input v-model="diagnosis_codes[2]"></b-form-input>
                    </b-form-group>
                </b-col>
                <b-col lg="6">
                    <b-form-group :label="`MCO / Payer Identifier (<a href='${EDI_CODE_GUIDE_URL}' target='_blank'>Code Guides: HHA</a>)`" label-for="payer_code">
                        <b-form-input v-model="form.medicaid_payer_id"></b-form-input>
                        <input-help :form="form" field="medicaid_payer_id" text="For use only if you are submitting a private pay claim for reimbursement (not common)"></input-help>
                    </b-form-group>
                </b-col>
                <b-col lg="6">
                    <b-form-group label="Plan Identifier">
                        <b-form-input v-model="form.medicaid_plan_id"></b-form-input>
                        <input-help :form="form" field="medicaid_plan_id" text="For use only if you are submitting a private pay claim for reimbursement (not common)"></input-help>
                    </b-form-group>
                </b-col>
            </b-row>
            <b-row>
                <b-col lg="12">
                    <b-btn variant="success" @click="updateInsuranceInfo()">Save Changes</b-btn>
                </b-col>
            </b-row>
        </b-card>

        <!-- Service Authorizations -->
        <b-card header="Service Authorizations"
                header-text-variant="white"
                header-bg-variant="info">
            <b-row>
                <b-col lg="6">
                    <b-form-group label="Maximum Weekly Hours" label-for="max_weekly_hours">
                        <div class="form-inline">
                            <b-form-input
                                    id="max_weekly_hours"
                                    type="number"
                                    step="any"
                                    v-model="form.max_weekly_hours"
                            >
                            </b-form-input>
                            <b-btn variant="success" @click="updateInsuranceInfo()">Save Changes</b-btn>
                        </div>
                        <input-help :form="form" field="max_weekly_hours" text="The maximum number of hours this client can be scheduled for per week."></input-help>
                    </b-form-group>
                </b-col>
                <b-col lg="6">
                    <b-form-group label="  ">
                    </b-form-group>
                </b-col>
            </b-row>
            <client-service-auth :clientId="client.id" :auths="auths" :services="services"></client-service-auth>
        </b-card>
    </div>
</template>

<script>
    import ClientServiceAuth from './ClientServiceAuth';
    import Constants from '../../../mixins/Constants';

    export default {
        props: ['client', 'auths', 'payers', 'services'],

        components: {ClientServiceAuth},
        mixins: [Constants],

        data() {
            return {
                form: new Form({
                    ltci_name: this.client.ltci_name,
                    ltci_address: this.client.ltci_address,
                    ltci_city: this.client.ltci_city,
                    ltci_state: this.client.ltci_state,
                    ltci_zip: this.client.ltci_zip,
                    ltci_policy: this.client.ltci_policy,
                    ltci_claim: this.client.ltci_claim,
                    ltci_phone: this.client.ltci_phone,
                    ltci_fax: this.client.ltci_fax,
                    medicaid_id: this.client.medicaid_id,
                    medicaid_diagnosis_codes: this.client.medicaid_diagnosis_codes || '',
                    medicaid_payer_id: this.client.medicaid_payer_id,
                    medicaid_plan_id: this.client.medicaid_plan_id,
                    max_weekly_hours: this.client.max_weekly_hours,
                }),
                diagnosis_codes: this.splitDiagnosisCodes(this.client.medicaid_diagnosis_codes || ''),
            }
        },

        methods: {
            updateInsuranceInfo() {
                this.form.medicaid_diagnosis_codes = this.joinDiagnosisCodes(this.diagnosis_codes);
                this.form.put('/business/clients/'+this.client.id+'/ltci');
            },
            joinDiagnosisCodes(input) {
                return input.filter(item => item).join(',');
            },
            splitDiagnosisCodes(input) {
                return input.split(',');
            },
        }
    }
</script>