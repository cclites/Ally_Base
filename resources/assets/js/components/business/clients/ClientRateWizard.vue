<template>
    <b-modal :title="defaultRate ? 'New Default Rate' : 'New Client Rate Wizard'"
             v-model="localValue"
             ref="rateWizardModal">
        <b-container fluid>
            <b-row>
                <b-col lg="12">
                    <template v-if="step === 1">
                        <h4>Caregiver Assignment</h4>
                        <p>
                            <strong>Which caregiver(s) do you want this rate applied to?</strong>
                        </p>

                        <b-form-radio-group v-model="caregiver_type">
                            <b-radio value="all">All Caregivers</b-radio><br />
                            <b-radio value="specific">A specific caregiver:</b-radio><br />
                        </b-form-radio-group>
                        <b-form-select v-if="caregiver_type === 'specific'" v-model="caregiver_select">
                            <option value="">--Select a specific caregiver--</option>
                            <option v-for="caregiver in caregivers" :value="caregiver.id">{{ caregiver.name }}</option>
                        </b-form-select>
                        <p>
                            <small v-if="caregiver_type === 'all'">Note: "All Caregivers" rates will only be used if there isn't a specific caregiver rate available.</small>
                        </p>
                    </template>
                    <template v-if="step === 2">
                        <h4>Service Assignment</h4>
                        <p>
                            <strong>Which service(s) do you want this rate applied to?</strong>
                        </p>

                        <b-form-radio-group v-model="service_type">
                            <b-radio value="all">All Services</b-radio><br />
                            <b-radio value="specific">A specific service:</b-radio><br />
                        </b-form-radio-group>
                        <b-form-select v-if="service_type === 'specific'" v-model="service_select">
                            <option value="">--Select a specific service--</option>
                            <option v-for="service in services" :value="service.id">{{ service.name }}</option>
                        </b-form-select>
                        <p>
                            <small v-if="service_type === 'specific'">Note: You must assign this service on the schedule for it to use this rate.</small>
                            <small v-else>Note: "All Services" rates will only be used if there isn't a specific service rate available.</small>
                        </p>
                    </template>
                    <template v-if="step === 3">
                        <h4>Payer Assignment</h4>
                        <p>
                            <strong>Which payer(s) do you want this rate applied to?</strong>
                        </p>

                        <b-form-radio-group v-model="payer_type">
                            <b-radio value="all">All Payers</b-radio><br />
                            <b-radio value="specific">A specific payer:</b-radio><br />
                        </b-form-radio-group>
                        <b-form-select v-if="payer_type === 'specific'" v-model="payer_select">
                            <option value="">--Select a specific payer--</option>
                            <option :value="0">({{ client.name }})</option>
                            <option v-for="payer in payers" :value="payer.id">{{ payer.name }}</option>
                        </b-form-select>
                        <p>
                            <small v-if="payer_type === 'specific'">Note: You must assign this payer on the schedule for it to use this rate.</small>
                            <small v-else>Note: "All Payers" rates will only be used if there isn't a specific payer rate available.</small>
                        </p>
                    </template>
                    <template v-if="step === 4">
                        <h4>Hourly Rate Assignment</h4>

                        <b-form-group label="What should the client be charged per hour?">
                            <b-form-input type="number" step="0.01" v-model="client_hourly"></b-form-input>
                            <small>If the client doesn't receive hourly services, you can set this to 0.</small>
                        </b-form-group>

                        <b-form-group label="What should the caregiver be paid per hour?">
                            <b-form-input type="number" step="0.01" v-model="caregiver_hourly"></b-form-input>
                            <small>If the client doesn't receive hourly services, you can set this to 0.  Otherwise, this should be less than what the client is charged.</small>
                        </b-form-group>
                    </template>

                    <template v-if="step === 5">
                        <h4>Fixed Rate Assignment</h4>

                        <b-form-group label="What should the client be charged per fixed shift?">
                            <b-form-input type="number" step="0.01" v-model="client_fixed"></b-form-input>
                            <small>If the client doesn't receive fixed or daily services, you can set this to 0.</small>
                        </b-form-group>

                        <b-form-group label="What should the caregiver be paid per fixed shift?">
                            <b-form-input type="number" step="0.01" v-model="caregiver_fixed"></b-form-input>
                            <small>If the client doesn't receive fixed or daily services, you can set this to 0.  Otherwise, this should be less than what the client is charged.</small>
                        </b-form-group>
                    </template>

                    <template v-if="step === 6">
                        <h4>Effective Date Range</h4>

                        <b-form-group label="When do you want this rate to go into effect?">
                            <mask-input v-model="start_date" type="date" class="date-input"></mask-input>
                            <small>Enter the start of the date range (ex. {{ today }})</small>
                        </b-form-group>

                        <b-form-group label="When do you want this rate to be valid until?">
                            <mask-input v-model="end_date" type="date" class="date-input"></mask-input>
                            <small>Enter the end of the date range.  If you are unsure, leave it at 12/31/9999 to remain in effect forever.</small>
                        </b-form-group>

                        <!--<small>Note: Once you press "Finish", you'll still have to save your changes in the Client Rates Table.  This makes sure you don't have any overlapping rates defined.</small>-->
                    </template>
                </b-col>
            </b-row>
        </b-container>
        <div slot="modal-footer">
            <b-btn variant="info" @click="step++" :disabled="!isNullOrInt(rateObject.caregiver_id)" v-if="step === 1">
                Continue
            </b-btn>
            <b-btn variant="info" @click="step++" :disabled="!isNullOrInt(rateObject.service_id)" v-if="step === 2">
                Continue
            </b-btn>
            <b-btn variant="info" @click="step++" :disabled="!isNullOrInt(rateObject.payer_id)" v-if="step === 3">
                Continue
            </b-btn>
            <b-btn variant="info" @click="step++" :disabled="!ratesAreValid(caregiver_hourly, client_hourly)" v-if="step === 4">
                Continue
            </b-btn>
            <b-btn variant="info" @click="step++" :disabled="!ratesAreValid(caregiver_fixed, client_fixed)" v-if="step === 5">
                Continue
            </b-btn>
            <b-btn variant="info" @click="finish()" :disabled="!datesAreValid()" v-if="step === 6">
                Finish
            </b-btn>
            <b-btn variant="primary" @click="step--" :disabled="step <= 1">Go Back</b-btn>
            <b-btn variant="default" @click="closeModal()">Cancel</b-btn>
        </div>
    </b-modal>
</template>

<script>
    const initialState = () => ({
        step: 1,
        caregiver_type: "all",
        caregiver_select: "",
        service_type: "all",
        service_select: "",
        payer_type: "all",
        payer_select: "",
        client_hourly: "",
        caregiver_hourly: "",
        client_fixed: "",
        caregiver_fixed: "",
        today: moment().format('MM/DD/YYYY'),
        start_date: moment().format('MM/DD/YYYY'),
        end_date: "12/31/9999",
    });

    export default {
        name: "ClientRateWizard",
        props: ["value", "client", "caregivers", "services", "payers", "defaultRate"],
        data() {
            return initialState();
        },
        computed: {
            localValue: {
                get() {
                    return this.value;
                },
                set(value) {
                    this.$emit('input', value);
                },
            },
            rateObject() {
                return {
                    caregiver_id: this.caregiver_type === 'all' ? null : this.caregiver_select,
                    service_id: this.service_type === 'all' ? null : this.service_select,
                    payer_id: this.payer_type === 'all' ? null : this.payer_select,
                    client_hourly_rate: this.client_hourly,
                    caregiver_hourly_rate: this.caregiver_hourly,
                    client_fixed_rate: this.client_fixed,
                    caregiver_fixed_rate: this.caregiver_fixed,
                    effective_start: this.start_date,
                    effective_end: this.end_date,
                }
            }
        },
        methods: {
            isNullOrInt(value) {
                return value === null || parseInt(value) >= 0;
            },
            datesAreValid() {
                return moment(this.rateObject.effective_start, 'MM/DD/YYYY', true).isValid()
                    && moment(this.rateObject.effective_end, 'MM/DD/YYYY', true).isValid()
            },
            ratesAreValid(caregiverRate, clientRate)
            {
                return caregiverRate.length != 0
                    && (caregiverRate == 0 || parseFloat(clientRate) > parseFloat(caregiverRate))
                    && parseFloat(clientRate) >= 0 && parseFloat(caregiverRate) >= 0;
            },
            resetState() {
                Object.assign(this.$data, initialState());
            },
            closeModal() {
                this.localValue = false;
                this.resetState();
            },
            finish() {
                this.$emit('new-rate', this.rateObject);
                this.closeModal();
            },
        },
        watch: {
            defaultRate(val, old) {
                if (val !== old) {
                    this.resetState();
                    this.step = val ? 4 : 1;
                }
            }
        }
    }
</script>

<style scoped>

</style>