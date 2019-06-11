<template>
    <b-modal :title="defaultRate ? 'New Default Rate' : 'Add Caregiver'"
             v-model="localValue"
             no-close-on-backdrop
             ref="rateWizardModal">
        <b-container fluid>
            <b-row>
                <b-col lg="12">
                    <template v-if="step === 1">
                        <h4>Select a Caregiver</h4>
                        <p>
                            <strong v-if="addMode">Which caregiver do you want to refer to the client?</strong>
                            <strong v-else>Which caregiver(s) do you want this rate applied to?</strong>
                        </p>

                        <!-- <b-form-radio-group v-model="caregiver_type">
                            <b-radio value="all">All Caregivers</b-radio><br />
                            <b-radio value="specific">A specific caregiver:</b-radio><br />
                        </b-form-radio-group> -->
                        <b-form-select v-if="addMode === true" v-model="caregiver_select">
                            <option value="">--Select a Caregiver--</option>
                            <option v-for="caregiver in getPotentialCaregivers" :value="caregiver.id" :key="caregiver.id">{{ caregiver.name }}</option>
                        </b-form-select>
                        <b-form-select v-else v-model="caregiver_select">
                            <option value="">--Select a Caregiver--</option>
                            <option v-for="caregiver in caregivers" :value="caregiver.id" :key="caregiver.id">{{ caregiver.nameLastFirst }}</option>
                        </b-form-select>
                        <p>
                            <small v-if="caregiver_type === 'all'">Note: "All Caregivers" rates will only be used if there isn't a specific caregiver rate available.</small>
                        </p>

                        <div class="form-check" v-if="addMode === true">
                            <label class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" name="remember" v-model="showAllCaregivers" />
                                <span class="custom-control-indicator"></span>
                                <span class="custom-control-description">Show Caregivers from all Office Locations</span>
                            </label>
                        </div>
                    </template>
                    <template v-if="step === 2">
                        <h4>Service Type</h4>
                        <p>
                            <strong>Will this caregivers's rate apply to all service types or to specific service types like respite or personal care?</strong>
                        </p>
                        <b-form-radio-group v-model="service_type">
                            <b-radio value="all">All Service Types (Common)</b-radio><br />
                            <b-radio value="specific">A Specific Service Type: (Medicaid/Insurance)</b-radio><br />
                        </b-form-radio-group>
                        <b-form-select v-if="service_type === 'specific'" v-model="service_select">
                            <option value="">--Select a specific service type--</option>
                            <option v-for="service in services" :value="service.id" :key="service.id">{{ service.name }}</option>
                        </b-form-select>
                        <p class="mt-2">
                            <small v-if="service_type === 'specific'">Note: You must assign this service on the schedule for it to use this rate.</small>
                            <small v-else>Note: Selecting ALL will be the default rate used if a caregiver clocks in/out of an unscheduled visit.</small>
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
                            <option v-for="payer in payers" :value="payer.id" :key="payer.id">{{ payer.name }}</option>
                        </b-form-select>
                        <p>
                            <small v-if="payer_type === 'specific'">Note: You must assign this payer on the schedule for it to use this rate.</small>
                            <small v-else>Note: "All Payers" rates will only be used if there isn't a specific payer rate available.</small>
                        </p>
                    </template>
                    <template v-if="step === 4">
                        <b-row>
                            <b-col lg="12">
                                <strong>Fill in two of the three fields below, Ally will automatically calculate the third field.</strong>
                                <hr />
                            </b-col>
                            <b-col lg="12">
                                <b-tabs>
                                    <b-tab title="Hourly Rates" active class="pt-3">
                                        <b-form-group label="Caregiver Hourly Rate" label-for="caregiver_hourly">
                                            <b-form-input
                                                    id="caregiver_hourly"
                                                    name="caregiver_hourly"
                                                    type="number"
                                                    step="0.01"
                                                    v-model="caregiver_hourly"
                                                    min="0"
                                                    @change="updateProviderHourlyRate"
                                            >
                                            </b-form-input>
                                            <small class="form-text text-muted">Enter the hourly rate for this caregiver.</small>
                                        </b-form-group>
                                        <b-form-group label="Registry Hourly Fee" label-for="provider_hourly">
                                            <b-form-input
                                                    id="provider_hourly"
                                                    name="provider_hourly"
                                                    type="number"
                                                    step="0.01"
                                                    v-model="provider_hourly"
                                                    min="0"
                                                    @change="updateClientHourlyRate"
                                            >
                                            </b-form-input>
                                            <small class="form-text text-muted">Enter the registry hourly fee.</small>
                                        </b-form-group>
                                        <b-form-group label="Ally Hourly Fee" label-for="ally_hourly">
                                            <b-form-input
                                                    id="ally_hourly"
                                                    name="ally_hourly"
                                                    type="number"
                                                    step="0.01"
                                                    :value="ally_hourly"
                                                    min="0"
                                                    disabled
                                            >
                                            </b-form-input>
                                        </b-form-group>
                                        <b-form-group label="Total Hourly Rate" label-for="total_hourly">
                                            <b-form-input
                                                    id="total_hourly"
                                                    name="total_hourly"
                                                    type="number"
                                                    step="0.01"
                                                    v-model="total_hourly"
                                                    min="0"
                                                    @change="updateProviderHourlyRate"
                                            >
                                            </b-form-input>
                                            <small class="form-text text-muted">The total hourly rate charged to the client.</small>
                                        </b-form-group>
                                    </b-tab>
                                    <b-tab title="Fixed/Daily Rates" class="pt-3">
                                        <b-form-group label="Caregiver Fixed/Daily Rate" label-for="caregiver_fixed">
                                            <b-form-input
                                                    id="caregiver_fixed"
                                                    name="caregiver_fixed"
                                                    type="number"
                                                    step="0.01"
                                                    v-model="caregiver_fixed"
                                                    min="0"
                                                    @change="updateProviderFixedRate"
                                            >
                                            </b-form-input>
                                            <small class="form-text text-muted">Enter the per-visit rate for this caregiver.</small>
                                        </b-form-group>
                                        <b-form-group label="Registry Fixed/Daily Fee" label-for="provider_fixed">
                                            <b-form-input
                                                    id="provider_fixed"
                                                    name="provider_fixed"
                                                    type="number"
                                                    step="0.01"
                                                    v-model="provider_fixed"
                                                    min="0"
                                                    @change="updateClientFixedRate"
                                            >
                                            </b-form-input>
                                            <small class="form-text text-muted">Enter the registry's per-visit fee.</small>
                                        </b-form-group>
                                        <b-form-group label="Ally Fixed/Daily Fee" label-for="ally_fixed">
                                            <b-form-input
                                                    id="ally_fixed"
                                                    name="ally_fixed"
                                                    type="number"
                                                    step="0.01"
                                                    :value="ally_fixed"
                                                    min="0"
                                                    disabled
                                            >
                                            </b-form-input>
                                        </b-form-group>
                                        <b-form-group label="Total Fixed/Daily Rate" label-for="total_fixed">
                                            <b-form-input
                                                    id="total_fixed"
                                                    name="total_fixed"
                                                    type="number"
                                                    step="0.01"
                                                    v-model="total_fixed"
                                                    min="0"
                                                    @change="updateProviderFixedRate"
                                            >
                                            </b-form-input>
                                            <small class="form-text text-muted">The total per-visit rate charged to the client.</small>
                                        </b-form-group>
                                    </b-tab>
                                </b-tabs>
                            </b-col>
                        </b-row>
                        <!-- <h4>Hourly Rate Assignment</h4>

                        <b-form-group label="What should the client be charged per hour?">
                            <b-form-input type="number" step="0.01" v-model="client_hourly"></b-form-input>
                            <small>If the client doesn't receive hourly services, you can set this to 0.</small>
                        </b-form-group>

                        <b-form-group label="What should the caregiver be paid per hour?">
                            <b-form-input type="number" step="0.01" v-model="caregiver_hourly"></b-form-input>
                            <small>If the client doesn't receive hourly services, you can set this to 0.  Otherwise, this should be less than what the client is charged.</small>
                        </b-form-group> -->
                    </template>

                    <!-- <template v-if="step === 5">
                        <h4>Fixed Rate Assignment</h4>

                        <b-form-group label="What should the client be charged per fixed shift?">
                            <b-form-input type="number" step="0.01" v-model="client_fixed"></b-form-input>
                            <small>If the client doesn't receive fixed or daily services, you can set this to 0.</small>
                        </b-form-group>

                        <b-form-group label="What should the caregiver be paid per fixed shift?">
                            <b-form-input type="number" step="0.01" v-model="caregiver_fixed"></b-form-input>
                            <small>If the client doesn't receive fixed or daily services, you can set this to 0.  Otherwise, this should be less than what the client is charged.</small>
                        </b-form-group>
                    </template> -->

                    <template v-if="step === 5">
                        <h4>Effective Date Range</h4>

                        <b-form-group label="When will this rate to go into effect?">
                            <mask-input v-model="start_date" type="date" class="date-input"></mask-input>
                            <small>Enter the start of the date range (ex. {{ today }})</small>
                        </b-form-group>

                        <b-form-group label="When will this rate expire?">
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
            <b-btn variant="info" @click="step++" :disabled="!ratesAreValid(caregiver_hourly, total_hourly) || !ratesAreValid(caregiver_fixed, total_fixed, true)" v-if="step === 4">
                Continue
            </b-btn>
            <!-- <b-btn variant="info" @click="step++" :disabled="!ratesAreValid(caregiver_fixed, client_fixed)" v-if="step === 5">
                Continue
            </b-btn> -->
            <b-btn variant="info" @click="finish()" :disabled="!datesAreValid()" v-if="step === 5">
                Finish
            </b-btn>
            <b-btn variant="primary" @click="step--" :disabled="step <= 1">Go Back</b-btn>
            <b-btn variant="default" @click="closeModal()">Cancel</b-btn>
        </div>
    </b-modal>
</template>

<script>
    import RateFactory from "../../../classes/RateFactory";

    const initialState = () => ({
        step: 1,
        caregiver_type: "specific",
        caregiver_select: "",
        service_type: "all",
        service_select: "",
        payer_type: "all",
        payer_select: "",
        // client_hourly: "",
        caregiver_hourly: "",
        provider_hourly: "",
        ally_hourly: "",
        total_hourly: "",
        // client_fixed: "",
        caregiver_fixed: "0.00",
        provider_fixed: "0.00",
        ally_fixed: "0.00",
        total_fixed: "0.00",
        today: moment().format('MM/DD/YYYY'),
        start_date: moment().subtract(1, 'week').format('MM/DD/YYYY'),
        end_date: "12/31/9999",
        showAllCaregivers: false,
    });

    export default {
        name: "ClientRateWizard",
        props: ["value", "client", "caregivers", "services", "payers", "defaultRate", 'addMode', 'potentialCaregivers', 'allyPctOriginal'],
        data() {
            return initialState();
        },
        computed: {
            getPotentialCaregivers() {
                if (this.showAllCaregivers) {
                    return this.potentialCaregivers;
                } else {
                    return this.potentialCaregivers.filter(cg => cg.businesses.includes(this.client.business_id));
                }
            },
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
                    client_hourly_rate: this.total_hourly,
                    caregiver_hourly_rate: this.caregiver_hourly,
                    client_fixed_rate: this.total_fixed,
                    caregiver_fixed_rate: this.caregiver_fixed,
                    effective_start: this.start_date,
                    effective_end: this.end_date,
                }
            },
            allyPct() {
                return this.paymentMethodDetail.allyRate || this.allyPctOriginal || 0.05;
            },
            paymentMethodDetail() {
                return this.$store.getters.getPaymentMethodDetail();
            },
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
            updateAllyHourlyFee() {
                this.ally_hourly = RateFactory.getAllyFee(this.allyPct, this.total_hourly).toFixed(2);
            },
            updateProviderHourlyRate() {
                this.updateAllyHourlyFee();
                let rate = RateFactory.getProviderFee(this.total_hourly, this.caregiver_hourly, this.allyPct);
                if (isNaN(rate)) {
                    return;
                }
                this.provider_hourly = rate.toFixed(2);
                this.highlightInput('#provider_hourly');
            },
            updateClientHourlyRate()
            {
                // debugger;
                let rate = RateFactory.getClientRate(this.provider_hourly, this.caregiver_hourly, this.allyPct);
                if (isNaN(rate)) {
                    return;
                }
                this.total_hourly = rate.toFixed(2);
                this.updateAllyHourlyFee();
                this.highlightInput('#total_hourly');
            },
            updateAllyFixedFee() {
                this.ally_fixed = RateFactory.getAllyFee(this.allyPct, this.total_fixed).toFixed(2);
            },
            updateProviderFixedRate() {
                this.updateAllyFixedFee();
                let rate = RateFactory.getProviderFee(this.total_fixed, this.caregiver_fixed, this.allyPct);
                if (isNaN(rate)) {
                    return;
                }
                this.provider_fixed = rate.toFixed(2);
                this.highlightInput('#provider_fixed');
            },
            updateClientFixedRate()
            {
                let rate = RateFactory.getClientRate(this.provider_fixed, this.caregiver_fixed, this.allyPct);
                if (isNaN(rate)) {
                    return;
                }
                this.total_fixed = rate.toFixed(2);
                this.updateAllyFixedFee();
                this.highlightInput('#total_fixed');
            },
            highlightInput(selector) {
                $(selector).addClass('highlight-input');
                setInterval(function() {
                    $(selector).removeClass('highlight-input');
                }, 300);
            },
        },
        watch: {
            defaultRate(val, old) {
                if (val !== old) {
                    this.resetState();
                    this.step = val ? 4 : 1;
                }
            },
            value() {
                this.resetState();
                this.step = 1;
            },
        }
    }
</script>

<style scoped>

</style>