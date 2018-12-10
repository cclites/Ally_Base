<template>
    <div>
        <b-row>
            <b-col>
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
                    </b-row>
                </b-card>
            </b-col>
        </b-row>
        <b-row>
            <b-col>
                <b-btn size="lg" variant="success" @click="updateInsuranceInfo()">Save</b-btn>
            </b-col>
        </b-row>
    </div>
</template>

<script>
    export default {
        props: ['client'],

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