<template>
    <b-card
        header="Caregivers"
        header-text-variant="white"
        header-bg-variant="info"
        >
        <b-card v-if="usingRateCodes">
            <h3>
                Default Client Rates
                <b-btn variant="info" size="sm" @click="rateCodeModal = true">Add a New Rate Code</b-btn>
            </h3>
            <loading-card v-if="!rateCodes"></loading-card>
            <b-form inline v-else>
                <b-form-group label="Hourly Rate: " label-for="hourly_rate_id">
                    <b-select v-model="rateForm.hourly_rate_id" class="ml-1 mr-2">
                        <option value="">No Default Rate</option>
                        <option v-for="code in hourlyRateCodes" :value="code.id" :key="code.id">{{ code.name }}</option>
                    </b-select>
                    <input-help :form="rateForm" field="hourly_rate_id"></input-help>
                </b-form-group>
                <b-form-group label="Fixed Rate: " label-for="fixed_rate_id" class="ml-1 mr-2">
                    <b-select v-model="rateForm.fixed_rate_id">
                        <option value="">No Default Rate</option>
                        <option v-for="code in fixedRateCodes" :value="code.id" :key="code.id">{{ code.name }}</option>
                    </b-select>
                    <input-help :form="rateForm" field="fixed_rate_id"></input-help>
                </b-form-group>
                <b-btn @click="saveDefaultRates()" variant="success">Save Default Rates</b-btn>
            </b-form>
        </b-card>
        <b-row class="mb-2">
            <b-col sm="6">
                <b-btn variant="info" @click="addCaregiver()">Add Caregiver to Client</b-btn>
                <b-btn variant="info" @click="clientExcludeCaregiverModal = true">Exclude Caregiver from Client</b-btn>
            </b-col>
            <b-col sm="6" class="text-right">
                {{ paymentText }}
            </b-col>
        </b-row>
        <div class="table-responsive">
            <table class="table table-bordered" id="client-cg-table">
                <thead>
                <tr>
                    <th>Referred Caregiver</th>
                    <th :class="getTdClass(1)">Rate Type</th>
                    <th :class="getTdClass(1)">Caregiver Rate</th>
                    <th :class="getTdClass(1)" v-if="clientRateStructure">Client Rate</th>
                    <th :class="getTdClass(1)" v-else>Provider Fee</th>
                    <th :class="getTdClass(1)">Ally Fee</th>
                    <th :class="getTdClass(1)">Total</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <template v-for="(item,index) in items">
                    <tr>
                        <td rowspan="2">{{ item.firstname }} {{ item.lastname }}</td>
                        <td :class="getTdClass(index)">Hourly</td>
                        <td :class="getTdClass(index)">{{ moneyFormat(item.rates.hourly.caregiver_rate) }}</td>
                        <td :class="getTdClass(index)" v-if="clientRateStructure">{{ moneyFormat(item.rates.hourly.client_rate) }}</td>
                        <td :class="getTdClass(index)" v-else>{{ moneyFormat(item.rates.hourly.provider_fee) }}</td>
                        <td :class="getTdClass(index)">{{ moneyFormat(item.rates.hourly.ally_fee) }}</td>
                        <td :class="getTdClass(index)">{{ moneyFormat(item.rates.hourly.total_rate) }}</td>
                        <!-- <td class="daily">{{ item.pivot.caregiver_fixed_rate }}</td> -->
                        <!-- <td class="daily">{{ item.pivot.provider_fixed_fee }}</td> -->
                        <!--<td :class=getTdClass(index)>{{ item.pivot.ally_daily_fee }}</td>-->
                        <!--<td class="daily">{{ item.pivot.total_daily_fee }}</td>-->
                        <td rowspan="2">
                            <b-btn size="sm" @click="editCaregiver(item)">Edit</b-btn>
                            <!--<b-btn size="sm" variant="danger" @click="removeAssignedCaregiver(item.id)">-->
                                <!--<i class="fa fa-times"></i>-->
                            <!--</b-btn>-->
                        </td>
                    </tr>
                    <tr v-if="item.rates.fixed.total_rate > 0">
                        <td :class="getTdClass(index)">Daily</td>
                        <td :class="getTdClass(index)">{{ moneyFormat(item.rates.fixed.caregiver_rate) }}</td>
                        <td :class="getTdClass(index)" v-if="clientRateStructure">{{ moneyFormat(item.rates.fixed.client_rate) }}</td>
                        <td :class="getTdClass(index)" v-else>{{ moneyFormat(item.rates.fixed.provider_fee) }}</td>
                        <td :class="getTdClass(index)">{{ moneyFormat(item.rates.fixed.ally_fee) }}</td>
                        <td :class="getTdClass(index)">{{ moneyFormat(item.rates.fixed.total_rate) }}</td>
                    </tr>
                    <tr v-else>
                        <td :class="getTdClass(index)">Daily</td>
                        <td colspan="4" :class="getTdClass(index)"></td>
                    </tr>
                </template>
                </tbody>
            </table>
            <hr>
            <div class="h6">Excluded Caregivers</div>
            <table class="table table-bordered excluded-caregivers" v-if="excludedCaregivers.length">
                <tr v-for="exGiver in excludedCaregivers">
                    <td class="sized">
                        {{ exGiver.caregiver.name }}
                    </td>
                    <td class="sized">{{ exGiver.note }}</td>
                    <td class="sized" style="white-space: nowrap">
                        <b-btn @click="removeExcludedCaregiver(exGiver.id)" class="mx-1" :variant="'primary'" v-if="loading !== exGiver.id">Remove From Excluded List</b-btn>
                        <div class="c-loader" v-if="loading === exGiver.id"></div>
                    </td>
                </tr>
            </table>
        </div>

        <rate-code-modal v-model="rateCodeModal" :code="{type: 'client'}" :type-locked="true" @saved="updateRateCode" />

        <b-modal id="clientCargiverSchedule"
                 title="Update Caregiver Schedule"
                 v-model="clientCargiverScheduleModal"
                 ok-title="OK">
            <b-container fluid>
                <b-row>
                    <b-col lg="12">
                        <div>Are you sure you want to update all <strong>{{ this.selectedCaregiver.firstname }} {{ this.selectedCaregiver.lastname }}</strong>'s future scheduled shifts with the new rate information?  This will update <strong>{{ this.selectedCaregiver.scheduled_shifts_count }}</strong> total shifts.</div>
                    </b-col>
                    <b-col lg="12" class="text-center">
                        <b-btn variant="danger" class="mt-4" @click.prevent="updateSchedules">Yes - Update all future shifts with this new rate</b-btn>
                    </b-col>
                </b-row>
            </b-container>
            <div slot="modal-footer">
               <b-btn variant="default" @click="clientCargiverScheduleModal = false">Cancel</b-btn>
            </div>
        </b-modal>

        <b-modal id="clientExcludeCargiver"
                 title="Exclude Caregiver"
                 v-model="clientExcludeCaregiverModal"
                 ok-title="Exclude"
                 @ok="excludeCaregiver">
            <b-container fluid>
                <b-row>
                    <b-col lg="12">
                        <b-form-group label="Caregiver *" label-for="exclude_caregiver_id">
                            <b-form-select
                                    id="exclude_caregiver_id"
                                    name="exclude_caregiver_id"
                                    v-model="excludeForm.caregiver_id"
                            >
                                <option value="">--Select a Caregiver--</option>
                                <option v-for="item in caregiverList" :value="item.id" :key="item.id">{{ item.name }}</option>
                            </b-form-select>
                        </b-form-group>
                        <b-form-group label="Note" label-for="note">
                            <b-form-textarea id="textarea1"
                                v-model="excludeForm.note"
                                :rows="3"
                                :max-rows="6">
                            </b-form-textarea>
                        </b-form-group>
                    </b-col>
                </b-row>
            </b-container>
            <div slot="modal-footer">
                <b-btn variant="default" @click="clientExcludeCaregiverModal=false">Close</b-btn>
                <b-btn variant="info" @click="excludeCaregiver()" v-if="excludeForm.caregiver_id">Exclude</b-btn>
            </div>
        </b-modal>

        <client-caregiver-rate-code-modal v-if="usingRateCodes"
                 v-model="clientCaregiverModal"
                 :client="client"
                 :caregiver="selectedCaregiver"
                 :caregiver-list="caregiverList"
                 :rate-structure="businessSettings().rate_structure"
                 :pivot="selectedCaregiver.pivot"
                 @saved="handleSavedCaregiver"
        />

        <b-modal v-else
                 :title="modalTitle"
                 v-model="clientCaregiverModal"
                 ref="clientCaregiverModal">
            <b-container fluid>
                <b-row v-if="!selectedCaregiver.id">
                    <b-col lg="12">
                        <b-form-group label="Caregiver" label-for="caregiver_id">
                            <select2
                                v-model="form.caregiver_id"
                                class="form-control"
                                >
                                <option value="">-- Select Caregiver --</option>
                                <option v-for="item in caregiverList" :value="item.id" :key="item.id">{{ item.name }}</option>
                            </select2>
                            <input-help :form="form" field="caregiver_id" text=""></input-help>
                        </b-form-group>
                    </b-col>
                </b-row>
                <b-row v-if="!form.caregiver_id">
                    <b-col lg="12">
                        <strong>Select a caregiver from the above list.</strong>
                    </b-col>
                </b-row>
                <b-row v-if="form.caregiver_id">
                    <b-col lg="12">
                        <strong>Fill in two of the three fields below, our system will automatically calculate the third field and the Ally fee.</strong>
                        <hr />
                    </b-col>
                    <b-col lg="12">

                        <b-tabs>
                            <b-tab title="Hourly Rates" active>
                                <b-form-group label="Caregiver Hourly Rate" label-for="caregiver_hourly_rate">
                                    <b-form-input
                                            id="caregiver_hourly_rate"
                                            name="caregiver_hourly_rate"
                                            type="number"
                                            v-model="form.caregiver_hourly_rate"
                                            min="0"
                                            @change="updateRatesFromCaregiverHourly"
                                    >
                                    </b-form-input>
                                    <input-help :form="form" field="caregiver_hourly_rate" text="Enter the hourly earnings for this caregiver."></input-help>
                                </b-form-group>

                                <b-form-group label="Registry Hourly Fee" label-for="provider_hourly_fee">
                                    <b-form-input
                                            id="provider_hourly_fee"
                                            name="provider_hourly_fee"
                                            type="number"
                                            v-model="form.provider_hourly_fee"
                                            min="0"
                                            @change="updateRatesFromCaregiverHourly"
                                    >
                                    </b-form-input>
                                    <input-help :form="form" field="provider_hourly_fee" text="Enter the registry hourly fee."></input-help>
                                </b-form-group>

                                <b-form-group label="Ally Hourly Fee" label-for="ally_hourly_fee">
                                    <b-form-input
                                            id="ally_hourly_fee"
                                            name="ally_hourly_fee"
                                            type="number"
                                            :value="ally_hourly_fee"
                                            min="0"
                                            disabled
                                    >
                                    </b-form-input>
                                </b-form-group>

                                <b-form-group label="Total Hourly Rate" label-for="total_hourly_rate">
                                    <b-form-input
                                            id="total_hourly_rate"
                                            name="total_hourly_rate"
                                            type="number"
                                            v-model="total_hourly_rate"
                                            min="0"
                                            @change="updateRatesFromTotalHourly"
                                    >
                                    </b-form-input>
                                    <input-help :form="form" field="total_hourly_rate" text="The total hourly rate charged to the client."></input-help>
                                </b-form-group>
                            </b-tab>
                            <b-tab title="Daily Rates (Live-in)" >
                                <b-form-group label="Caregiver Daily Rate" label-for="caregiver_hourly_rate">
                                    <b-form-input
                                            id="caregiver_fixed_rate"
                                            name="caregiver_fixed_rate"
                                            type="number"
                                            v-model="form.caregiver_fixed_rate"
                                            min="0"
                                            @change="updateRatesFromCaregiverDaily"
                                    >
                                    </b-form-input>
                                    <input-help :form="form" field="caregiver_fixed_rate" text="Enter the daily earnings for this caregiver."></input-help>
                                </b-form-group>

                                <b-form-group label="Registry Daily Fee" label-for="provider_fixed_fee">
                                    <b-form-input
                                            id="provider_fixed_fee"
                                            name="provider_fixed_fee"
                                            type="number"
                                            v-model="form.provider_fixed_fee"
                                            min="0"
                                            @change="updateRatesFromCaregiverDaily"
                                    >
                                    </b-form-input>
                                    <input-help :form="form" field="provider_fixed_fee" text="Enter the registry daily fee."></input-help>
                                </b-form-group>

                                <b-form-group label="Ally Daily Fee" label-for="ally_daily_fee">
                                    <b-form-input
                                            id="ally_daily_fee"
                                            name="ally_daily_fee"
                                            type="number"
                                            :value="ally_daily_fee"
                                            min="0"
                                            disabled
                                    >
                                    </b-form-input>
                                </b-form-group>

                                <b-form-group label="Total Daily Rate" label-for="total_daily_rate">
                                    <b-form-input
                                            id="total_daily_rate"
                                            name="total_daily_rate"
                                            type="number"
                                            v-model="total_daily_rate"
                                            min="0"
                                            @change="updateRatesFromTotalDaily"
                                    >
                                    </b-form-input>
                                    <input-help :form="form" field="total_daily_rate" text="The total daily rate charged to the client."></input-help>
                                </b-form-group>
                            </b-tab>
                        </b-tabs>

                    </b-col>
               </b-row>
                <b-row v-if="this.selectedCaregiver.id">
                    <b-col>
                        <hr />
                        <b-form-group>
                            <b-btn variant="danger" @click="removeAssignedCaregiver(form.caregiver_id)">
                                Remove from Client
                            </b-btn>
                        </b-form-group>
                    </b-col>
                </b-row>
            </b-container>
            <div slot="modal-footer">
               <b-btn variant="default" @click="clientCaregiverModal=false">Close</b-btn>
               <b-btn variant="warning" @click="saveCaregiver(true)" v-if="selectedCaregiver && selectedCaregiver.id">Save and Update Future Schedules</b-btn>
               <b-btn variant="info" @click="saveCaregiver()" v-if="form.caregiver_id">Save</b-btn>
            </div>
        </b-modal>

    </b-card>
</template>

<script>
    import FormatsNumbers from '../../../mixins/FormatsNumbers'
    import BusinessSettings from "../../../mixins/BusinessSettings";
    import RateCodes from "../../../mixins/RateCodes";
    import RateCodeModal from "../rate_codes/RateCodeModal";
    import ClientCaregiverRateCodeModal from "./ClientCaregiverRateCodeModal";

    export default {
        props: {
            'client': Object,
            'allyRateOriginal': Number,
            'paymentTypeMessage': {
                default() {
                    return '';
                }
            }
        },

        components: {ClientCaregiverRateCodeModal, RateCodeModal},

        mixins: [FormatsNumbers, BusinessSettings, RateCodes],

        data() {
            return {
                client_id: this.client.id,
                caregiverList: [],
                items: [],
                clientCaregiverModal: false,
                clientExcludeCaregiverModal: false,
                clientCargiverScheduleModal: false,
                selectedCaregiver: {pivot: {}},
                form: new Form(),
                excludeForm: this.makeExcludeForm(),
                excludedCaregivers: [],
                ally_hourly_fee: 0.00,
                ally_daily_fee: 0.00,
                total_hourly_rate: 0.00,
                total_daily_rate: 0.00,
                loading: '',
                rateCodeModal: false,
                rateForm: new Form({
                    'hourly_rate_id': this.client.hourly_rate_id || "",
                    'fixed_rate_id': this.client.fixed_rate_id || "",
                })
            }
        },

        mounted() {
            this.fetchAssignedCaregivers();
            this.fetchCaregivers();
            this.fetchExcludedCaregivers();
            this.fetchRateCodes('client');
        },
        
        computed: {
            modalTitle() {
                if (this.selectedCaregiver.id) {
                    return 'Edit Caregiver Assignment';
                }
                return 'Add Caregiver to Client';
            },

            paymentText() {
                const paymentMethodDetail = this.$store.getters.getPaymentMethodDetail();
                return paymentMethodDetail.paymentText || this.paymentTypeMessage;
            },

            allyRate() {
                const paymentMethodDetail = this.$store.getters.getPaymentMethodDetail();
                return paymentMethodDetail.allyRate || this.allyRateOriginal;
            },

            paymentMethodDetail() {
                return this.$store.getters.getPaymentMethodDetail();
            }
        },

        watch: {
            paymentMethodDetail(newData, oldData) {
                this.items = [];
                this.fetchAssignedCaregivers();
            },
        },

        methods: {
            addCaregiver() {
                this.selectedCaregiver = {pivot: {}};
                this.form = new Form({
                    caregiver_id: null,
                    caregiver_hourly_rate: 0.00,
                    caregiver_fixed_rate: 0.00,
                    provider_hourly_fee: 0.00,
                    provider_fixed_fee: 0.00,
                });
                this.clientCaregiverModal = true;
                this.updateRatesFromCaregiverHourly();
                this.updateRatesFromCaregiverDaily();
            },

            editCaregiver(item) {
                this.selectedCaregiver = item;
                this.form = new Form({
                    caregiver_id: item.id,
                    caregiver_hourly_rate: item.pivot.caregiver_hourly_rate,
                    caregiver_fixed_rate: item.pivot.caregiver_fixed_rate,
                    provider_hourly_fee: item.pivot.provider_hourly_fee,
                    provider_fixed_fee: item.pivot.provider_fixed_fee,
                });
                this.clientCaregiverModal = true;
                this.updateRatesFromCaregiverHourly();
                this.updateRatesFromCaregiverDaily();
            },

            confirmUpdateSchedule(item) {
                this.selectedCaregiver = item;
                this.form = new Form({
                    caregiver_id: item.id,
                });
                this.clientCargiverScheduleModal = true;
            },

            saveCaregiver(updateSchedules = false) {
                let component = this;
                this.form.post('/business/clients/' + component.client_id + '/caregivers')
                    .then((response) => {
                        component.handleSavedCaregiver(response.data.data);

                        if (updateSchedules && response.data.data.scheduled_shifts_count > 0) {
                            component.confirmUpdateSchedule(response.data.data)
                        }
                    });
            },

            handleSavedCaregiver(caregiver) {
                this.fetchCaregivers();
                let index = this.items.findIndex(item => {
                    return caregiver.id == item.id;
                });
                if (index > -1) {
                    Vue.set(this.items, index, caregiver);
                }
                else {
                    this.items.unshift(caregiver);
                }
                this.clientCaregiverModal = false;
            },

            saveDefaultRates() {
                this.rateForm.put('/business/clients/' + this.client_id + '/default-rates');
            },

            updateSchedules() {
                let component = this;
                this.form.post('/business/clients/' + component.client_id + '/caregivers/' + this.selectedCaregiver.id + '/schedule')
                    .then((response) => {
                        component.clientCargiverScheduleModal = false;
                    })
            },

            fetchAssignedCaregivers() {
                axios.get('/business/clients/' + this.client_id + '/caregivers')
                    .then(response => {
                        if (Array.isArray(response.data)) {
                            this.items = _.sortBy(response.data, ['lastname', 'firstname']);
                        } else {
                            this.items = [];
                        }
                    });
            },

            fetchCaregivers() {
                axios.get('/business/clients/' + this.client_id + '/potential-caregivers')
                    .then(response => {
                        this.caregiverList = response.data;
                    });
            },

            fetchExcludedCaregivers() {
                axios.get('/business/clients/'+this.client_id+'/excluded-caregivers')
                    .then(response => {
                        this.excludedCaregivers = response.data;
                    }).catch(error => {
                        console.error(error.response);
                    });
            },

            makeExcludeForm() {
                return new Form({
                    caregiver_id: "",
                    note: "",
                });
            },

            async excludeCaregiver() {
                const response = await this.excludeForm.post('/business/clients/'+this.client_id+'/exclude-caregiver');
                this.fetchExcludedCaregivers();
                this.fetchCaregivers();
                this.excludeForm = this.makeExcludeForm();
                this.clientExcludeCaregiverModal = false;
            },

            removeExcludedCaregiver(id) {
                this.loading = id;
                axios.delete('/business/clients/excluded-caregiver/'+id)
                    .then(response => {
                        this.loading = '';
                        this.fetchExcludedCaregivers();
                        this.fetchCaregivers();
                    }).catch(error => {
                    this.loading = '';
                        console.error(error.response);
                    });
            },

            removeAssignedCaregiver(caregiver_id) {
                console.log('Removing caregiver from client.');
                let form = new Form({caregiver_id: caregiver_id});
                form.post('/business/clients/'+this.client_id+'/detach-caregiver')
                    .then(() => {
                        this.fetchAssignedCaregivers();
                        if (this.clientCaregiverModal) {
                            this.clientCaregiverModal = false;
                        }
                    });
            },

            updateAllyHourlyFee() {
                let cgRate = parseFloat(this.form.caregiver_hourly_rate);
                let provFee = parseFloat(this.form.provider_hourly_fee);
                if (isNaN(cgRate) || isNaN(provFee)) {
                    this.ally_hourly_fee = 0;
                    return;
                }
                let computed = (cgRate + provFee) * this.allyRate;
                this.ally_hourly_fee = computed.toFixed(2);
            },

            updateRatesFromCaregiverHourly() {
                this.updateAllyHourlyFee();
                let cgRate = parseFloat(this.form.caregiver_hourly_rate);
                let provFee = parseFloat(this.form.provider_hourly_fee);
                let allyFee = parseFloat(this.ally_hourly_fee);
                if (isNaN(cgRate) || isNaN(provFee)) {
                    return;
                }
                let computed = cgRate + provFee + allyFee;
                this.total_hourly_rate = computed.toFixed(2);
                this.highlightInput('#total_hourly_rate');
            },

            updateRatesFromTotalHourly() {
                let cgRate = parseFloat(this.form.caregiver_hourly_rate);
                let totalRate = parseFloat(this.total_hourly_rate);
                if (isNaN(cgRate) || isNaN(totalRate)) {
                    return;
                }
                console.log(totalRate, 1+parseFloat(this.allyRate), cgRate);
                let computed = totalRate / (1+parseFloat(this.allyRate)) - cgRate;
                this.form.provider_hourly_fee = computed.toFixed(2);
                this.highlightInput('#provider_hourly_fee');
                this.updateAllyHourlyFee();
            },

            updateAllyDailyFee() {
                let cgRate = parseFloat(this.form.caregiver_fixed_rate);
                let provFee = parseFloat(this.form.provider_fixed_fee);
                if (isNaN(cgRate) || isNaN(provFee)) {
                    this.ally_daily_fee = 0;
                    return;
                }
                let computed = (cgRate + provFee) * this.allyRate;
                this.ally_daily_fee = computed.toFixed(2);
            },

            updateRatesFromCaregiverDaily() {
                this.updateAllyDailyFee();
                let cgRate = parseFloat(this.form.caregiver_fixed_rate);
                let provFee = parseFloat(this.form.provider_fixed_fee);
                let allyFee = parseFloat(this.ally_daily_fee);
                if (isNaN(cgRate) || isNaN(provFee)) {
                    return;
                }
                let computed = cgRate + provFee + allyFee;
                this.total_daily_rate = computed.toFixed(2);
                this.highlightInput('#total_daily_rate');
            },

            updateRatesFromTotalDaily() {
                let cgRate = parseFloat(this.form.caregiver_fixed_rate);
                let totalRate = parseFloat(this.total_daily_rate);
                if (isNaN(cgRate) || isNaN(totalRate)) {
                    return;
                }
                console.log(totalRate, 1+parseFloat(this.allyRate), cgRate);
                let computed = totalRate / (1+parseFloat(this.allyRate)) - cgRate;
                this.form.provider_fixed_fee = computed.toFixed(2);
                this.highlightInput('#provider_fixed_fee');
                this.updateAllyDailyFee();
            },

            highlightInput(selector) {
                $(selector).addClass('highlight-input');
                setInterval(function() {
                    $(selector).removeClass('highlight-input');
                }, 300);
            },

            getTdClass(index) {
                return index % 2 === 0 ? 'darker' : 'lighter';
            }
        },

    }
</script>

<style lang="scss">
    th.darker, td.darker {
        background-color: #eee;
        text-align: center;
    }
    th.lighter, td.lighter {
        background-color: #f5f5f5;
        text-align: center;
    }
    thead th {
        padding: .4rem .75rem !important;
    }
    .highlight-input {
        border: 1px solid blue;
        outline: 2px solid #ddd;
    }

    .excluded-caregivers {
        border: 0;
        width: 100%;

        td {
            vertical-align: top;

            &.sized {
                min-width: 80px;
                max-width: 240px;
                width: 150px;
            }

            button.sized {
                text-overflow: ellipsis;
                overflow: hidden;
                max-width: 85%;
            }
        }
    }

    .c-loader {
        width: 30px;
        height: 30px;
        border-width: 5px !important;
        margin: 0 auto;
    }
</style>
