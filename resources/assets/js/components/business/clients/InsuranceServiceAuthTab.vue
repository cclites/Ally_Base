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
        <b-card
            header="Service Orders"
            header-text-variant="white"
            header-bg-variant="info"
            >
            <form @submit.prevent="save()" @keydown="form.clearError($event.target.name)">
                <b-row>
                    <b-col lg="6">
                        <b-form-group label="Maximum Weekly Hours" label-for="max_weekly_hours">
                            <b-form-input
                                id="max_weekly_hours"
                                type="number"
                                step="any"
                                v-model="form.max_weekly_hours"
                                >
                            </b-form-input>
                            <input-help :form="form" field="max_weekly_hours" text="The maximum number of hours this client can be scheduled for per week."></input-help>
                        </b-form-group>
                    </b-col>
                </b-row>
            </form>
        </b-card>
        <b-card header="Service Authorization"
                header-text-variant="white"
                header-bg-variant="info">
            <b-row>
                <b-col lg="6">
                    <b-form-group label="Service ID">
                        <b-form-select v-model="form.service_id" class="mr-1 mb-1" name="report_type">
                            <option v-for="s in services" :value="s.id" :key="s.id">{{ s.name }}</option>
                        </b-form-select>
                    </b-form-group>
                </b-col>
                <b-col lg="6">
                    <b-form-group label="Player ID">
                        <b-form-select v-model="form.payer_id" class="mr-1 mb-1" name="report_type">
                            <option v-for="p in payers" :value="p.id" :key="p.id">{{ p.name }}</option>
                        </b-form-select>
                    </b-form-group>
                </b-col>
                <b-col lg="6">
                    <b-form-group label="Effective Start">
                        <date-picker v-model="form.effective_start"></date-picker>
                    </b-form-group>
                </b-col>
                <b-col lg="6">
                    <b-form-group label="Effective End">
                        <date-picker v-model="form.effective_end"></date-picker>
                    </b-form-group>
                </b-col>
                <b-col lg="6">
                    <b-form-group label="Units">
                        <b-form-input v-model="form.units"></b-form-input>
                    </b-form-group>
                </b-col>
                <b-col lg="6">
                    <b-form-group label="Unit Type">
                        <b-form-select v-model="form.unit_type" class="mr-1 mb-1" name="report_type">
                            <option value="hourly">Hourly</option>
                            <option value="fixed">Fixed</option>
                        </b-form-select>
                    </b-form-group>
                </b-col>
                <b-col lg="6">
                    <b-form-group label="Period">
                        <b-form-select v-model="form.period" class="mr-1 mb-1" name="report_type">
                            <option value="daily">Daily</option>
                            <option value="weekly">Weekly</option>
                            <option value="monthly">Monthly</option>
                        </b-form-select>
                    </b-form-group>
                </b-col>
                <b-col lg="6">
                    <b-form-group label="Notes">
                        <b-form-input v-model="form.notes"></b-form-input>
                    </b-form-group>
                </b-col>
            </b-row>
        </b-card>
        <b-btn variant="success" @click="updateInsuranceInfo()">Save</b-btn> 
    </div>
</template>

<script>
    export default {
        props: ['client', 'auth', 'payers', 'services'],

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
                    max_weekly_hours: this.client.max_weekly_hours,
                    service_id: this.auth.service_id,
                    payer_id: this.auth.payer_id,
                    effective_start: this.auth.effective_start,
                    effective_end: this.auth.effective_end,
                    units: this.auth.units,
                    unit_type: this.auth.unit_type,
                    period: this.auth.period,
                    notes: this.auth.notes,
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