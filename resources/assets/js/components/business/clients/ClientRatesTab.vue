<template>
    <b-card
        header="Caregivers &amp; Rates"
        header-text-variant="white"
        header-bg-variant="info"
        >
        <div class="d-flex">
            <div class="mb-3">
                <h5><strong>Referred Caregivers and Rates</strong></h5>
            </div>
            <div class="ml-auto">
                {{ paymentText }}
            </div>
        </div>
        <div class="mb-3">
            <b-btn variant="info" @click="addCaregiver()">Add Caregiver</b-btn>
            <b-btn variant="info" @click="clientExcludeCaregiverModal = true">Exclude Caregiver</b-btn>
            <b-btn variant="info" @click="addRateWizard()">Add Rate to Existing Caregiver</b-btn>
            <!-- <b-btn variant="primary" @click="addRateWizard(true)" class="ml-2">Add a Default Client Rate</b-btn> -->
        </div>

        <div v-if="filterByCaregiverId">
            Active Filters (Filters break form input keys):
            <b-badge pill
                     size="lg"
                     variant="light">
                Caregiver {{ filterByCaregiverId }}

                <a href="javascript:void(0)" @click="filterByCaregiverId = null"><i class="fa fa-times"></i></a>
            </b-badge>
        </div>

        <div class="table-responsive mb-2">
            <b-table bordered striped hover show-empty
                     :items="filteredItems"
                     :fields="fields"
                     :current-page="currentPage"
                     :per-page="perPage"
                     :sort-by.sync="sortBy"
                     :sort-desc.sync="sortDesc"
                     ref="table"
                     class="table-fit-more"
            >
                <template slot="caregiver_id" scope="row">
                    {{ row.item.caregiver_name }}
                    <!-- <b-select v-model="row.item.caregiver_id" size="sm">
                        <option :value="null">(All)</option>
                        <option v-for="item in caregivers" :value="item.id" :key="item.id" v-if="!filterByCaregiverId || filterByCaregiverId === item.id">{{ item.name }}</option>
                    </b-select> -->
                </template>
                <template slot="service_id" scope="row">
                    <b-select v-model="row.item.service_id" size="sm" @change="(e) => onChangeService(e, row.item)">
                        <option :value="null">(All)</option>
                        <option v-for="service in services" :value="service.id" :key="service.id">{{ service.name }}</option>
                    </b-select>
                </template>
                <template slot="payer_id" scope="row">
                    <b-select v-model="row.item.payer_id" size="sm" @change="(e) => onChangePayer(e, row.item)">
                        <option :value="null">(All)</option>
                        <option v-for="item in payers" :value="item.id" :key="item.id">{{ item.name }}</option>
                    </b-select>
                </template>
                <template slot="effective_start" scope="row">
                    <mask-input v-model="row.item.effective_start" type="date" class="date-input form-control-sm"></mask-input>
                </template>
                <template slot="effective_end" scope="row">
                    <mask-input v-model="row.item.effective_end" type="date" class="date-input form-control-sm"></mask-input>
                </template>
                <template slot="caregiver_hourly_rate" scope="row">
                    <b-form-input name="caregiver_hourly_rate"
                        class="money-input"
                        type="number"
                        step="0.01"
                        min="0"
                        max="999.99"
                        required
                        v-model="row.item.caregiver_hourly_rate"
                        size="sm"
                    ></b-form-input>
                </template>
                <template slot="caregiver_fixed_rate" scope="row">
                    <b-form-input name="caregiver_fixed_rate"
                        class="money-input"
                        type="number"
                        step="0.01"
                        min="0"
                        max="999.99"
                        required
                        v-model="row.item.caregiver_fixed_rate"
                        size="sm"
                    ></b-form-input>
                </template>
                <template slot="client_hourly_rate" scope="row">
                    <b-form-input name="client_hourly_rate"
                        class="money-input"
                        type="number"
                        step="0.01"
                        min="0"
                        max="999.99"
                        required
                        v-model="row.item.client_hourly_rate"
                        size="sm"
                    ></b-form-input>
                </template>
                <template slot="client_fixed_rate" scope="row">
                    <b-form-input name="client_fixed_rate"
                        class="money-input"
                        type="number"
                        step="0.01"
                        min="0"
                        max="999.99"
                        required
                        v-model="row.item.client_fixed_rate"
                        size="sm"
                    ></b-form-input>
                </template>
                <template slot="actions" scope="data">
                    <b-btn size="sm" @click="removeRate(data.item)" :disabled="busyRemoving === data.item.id">
                        <i v-if="busyRemoving === data.item.id" class="fa fa-spinner fa-spin"></i>
                        <i v-else class="fa fa-trash"></i>
                    </b-btn>
                </template>
                <template slot="provider_hourly_fee" scope="row">
                    {{ getProviderFee(row.item.client_hourly_rate, row.item.caregiver_hourly_rate) }}
                </template>
                <template slot="ally_hourly_fee" scope="row">
                    {{ getAllyFee(row.item.client_hourly_rate) }}
                </template>
                <template slot="provider_fixed_fee" scope="row">
                    {{ getProviderFee(row.item.client_fixed_rate, row.item.caregiver_fixed_rate) }}
                </template>
                <template slot="ally_fixed_fee" scope="row">
                    {{ getAllyFee(row.item.client_fixed_rate) }}
                </template>
            </b-table>
        </div>
        <b-btn id="save-rates" @click="saveRates()" variant="success">Save Client Rates</b-btn>

        <div class="mt-4"><small>* Provider fees and Ally fees are estimated based on the primary payment method.</small></div>

        <hr />
        <div class="mt-4">
            <h5>
                <strong>Excluded Caregivers </strong>
            </h5>
        </div>
        <div class="table-responsive">
            <b-table bordered striped hover show-empty
                :items="excludedCaregivers"
                :fields="excludedFields"
                empty-text="There are no excluded caregivers for this client."
            >
                <template slot="actions" scope="data">
                    <b-btn @click="editExcludedCaregiver(data.item)" size="sm" variant="info"><i class="fa fa-edit"></i></b-btn>
                    <b-btn @click="removeExcludedCaregiver(data.item.id)" size="sm" variant="danger"><i class="fa fa-times"></i></b-btn>
                </template>
            </b-table>
        </div>

        <!-- MODALS -->
        <b-modal id="clientExcludeCargiver"
                 :title="excludeForm.id ? 'Update Excluded Caregiver' : 'Exclude Caregiver'"
                 v-model="clientExcludeCaregiverModal">
            <b-container fluid>
                <b-row>
                    <b-col lg="12">
                        <b-form-group label="Caregiver *" label-for="exclude_caregiver_id">
                            <div v-if="excludeForm.id">
                                {{ excludeForm.caregiver_name }}
                            </div>
                            <b-form-select v-else name="exclude_caregiver_id" v-model="excludeForm.caregiver_id">
                                <option value="">--Select a Caregiver--</option>
                                <option v-for="item in otherCaregivers" :value="item.id" :key="item.id">{{ item.name }}</option>
                            </b-form-select>
                        </b-form-group>
                        <b-form-group label="Reason" label-for="exclude_reason">
                            <b-form-select name="exclude_reason" v-model="excludeForm.reason">
                                <option value="">--Reason for excluding--</option>
                                <option v-for="(item, index) in exclusionReasons" :value="index" :key="index">{{ item }}</option>
                            </b-form-select>
                        </b-form-group>
                        <b-form-group label="Note" label-for="note">
                            <b-form-textarea v-model="excludeForm.note"
                                             :rows="3">
                            </b-form-textarea>
                        </b-form-group>
                        <b-form-group label="Effective Date" label-for="exclude_effective_at">
                            <mask-input v-model="excludeForm.effective_at" id="exclude_effective_at" type="date"></mask-input>
                        </b-form-group>
                    </b-col>
                </b-row>
            </b-container>
            <div slot="modal-footer">
                <b-btn variant="default" @click="clientExcludeCaregiverModal=false">Close</b-btn>
                <b-btn variant="info" @click="excludeCaregiver()" :disabled="!excludeForm.caregiver_id">
                    {{ excludeForm.id ? 'Save' : 'Exclude Caregiver' }}
                </b-btn>
            </div>
        </b-modal>

        <!-- <b-modal title="Add Caregiver Assignment"
                 v-model="clientCaregiverModal"
                 ref="clientCaregiverModal">
            <b-container fluid>
                <b-row>
                    <b-col lg="12">
                        <b-form-group label="Caregiver" label-for="caregiver_id">
                            <select2
                                    v-model="caregiverForm.caregiver_id"
                                    class="form-control"
                            >
                                <option value="">-- Select Caregiver --</option>
                                <option v-for="item in otherCaregivers" :value="item.id" :key="item.id">{{ item.name }}</option>
                            </select2>
                            <input-help :form="caregiverForm" field="caregiver_id" text=""></input-help>
                        </b-form-group>
                    </b-col>
                </b-row>
            </b-container>
            <div slot="modal-footer">
                <b-btn variant="default" @click="clientCaregiverModal=false">Close</b-btn>
                <b-btn variant="info" @click="saveCaregiver()" :disabled="!caregiverForm.caregiver_id">Add Caregiver</b-btn>
            </div>
        </b-modal> -->

        <client-rate-wizard v-model="rateWizardModal"
                            :client="client"
                            :caregivers="caregivers"
                            :services="services"
                            :payers="payers"
                            :default-rate="defaultRateOnWizard"
                            :potential-caregivers="otherCaregivers"
                            :add-mode="addNewCaregiver"
                            :ally-rate-original="allyRateOriginal"
                            @new-rate="addRate">
        </client-rate-wizard>
    </b-card>
</template>

<script>
    import FormatsDates from "../../../mixins/FormatsDates";
    import RateFactory from "../../../classes/RateFactory";
    import ClientRateWizard from "./ClientRateWizard";

    export default {
        components: {ClientRateWizard},
        props: {
            'client': {},
            'rates': Array,
            'allyRateOriginal': Number,
            'paymentTypeMessage': {
                default() {
                    return '';
                }
            }
        },

        mixins: [ FormatsDates ],

        data() {
            return {
                payers: [],
                caregivers: [],
                excludedCaregivers: [],
                otherCaregivers: [],
                services: [],

                filterByCaregiverId: null,

                clientCaregiverModal: false,
                clientExcludeCaregiverModal: false,
                rateWizardModal: false,
                defaultRateOnWizard: false,
                addNewCaregiver: false,

                excludeForm: new Form({
                    id: "",
                    caregiver_name: "",
                    caregiver_id: "",
                    note: "",
                    reason: '',
                    effective_at: moment().format('MM/DD/YYYY'),
                }),
                caregiverForm: new Form({caregiver_id: ""}),
                busyRemoving: null,

                items: [],
                totalRows: 0,
                perPage: 30,
                currentPage: 1,
                sortBy: 'caregiver_name',
                sortDesc: false,
                fields: [
                    {
                        key: 'caregiver_name',
                        label: 'Caregiver',
                        sortable: true
                    },
                    {
                        key: 'service_id',
                        label: 'Service',
                        sortable: true
                    },
                    {
                        key: 'payer_id',
                        label: 'Payer',
                        sortable: true
                    },
                    {
                        key: 'effective_start',
                        label: 'Effective Start',
                        sortable: true,
                    },
                    {
                        key: 'effective_end',
                        label: 'Effective End',
                        sortable: true,
                    },
                    {
                        key: 'client_hourly_rate',
                        label: 'Client Hourly Rate',
                        sortable: true,
                    },
                    {
                        key: 'caregiver_hourly_rate',
                        label: 'CG Hourly Rate',
                        sortable: true,
                    },
                    {
                        key: 'provider_hourly_fee',
                        label: 'Provider Hourly Fee*'
                    },
                    {
                        key: 'ally_hourly_fee',
                        label: 'Ally Hourly Fee*'
                    },
                    {
                        key: 'client_fixed_rate',
                        label: 'Client Fixed/Daily Rate',
                        sortable: true,
                    },
                    {
                        key: 'caregiver_fixed_rate',
                        label: 'CG Fixed/Daily Rate',
                        sortable: true,
                    },
                    {
                        key: 'provider_fixed_fee',
                        label: 'Provider Fixed/Daily Fee*'
                    },
                    {
                        key: 'ally_fixed_fee',
                        label: 'Ally Fixed/Daily Fee*'
                    },
                    {
                        key: 'actions',
                        label: '',
                        class: 'hidden-print'
                    },
                ],
                excludedFields: [
                    { key: 'caregiver_name', label: 'Name', sortable: true },
                    { key: 'effective_at', label: 'Date Excluded', sortable: true, formatter: x => this.formatDateFromUTC(x) },
                    { key: 'reason', label: 'Reason Code', sortable: true, formatter: x => { return x ? this.exclusionReasons[x] : 'None' } },
                    { key: 'note', sortable: true },
                    { key: 'actions', label: '', class: 'hidden-print' },
                ],
                exclusionReasons: {
                    'unhappy_client': 'Client not happy and refuses service from this caregiver',
                    'retired': 'Retired',
                    'no_shows': 'Continual no shows',
                    'quit': 'Caregiver quit',
                    'service_not_needed': 'Client no longer needs service',
                },
            }
        },

        computed: {
            paymentText() {
                return this.paymentMethodDetail.payment_text || this.paymentTypeMessage;
            },

            allyRate() {
                return this.paymentMethodDetail.allyRate || this.allyRateOriginal;
            },

            paymentMethodDetail() {
                return this.$store.getters.getPaymentMethodDetail();
            },

            filteredItems() {
                let rates = this.items;
                let filtered = false;

                if (this.filterByCaregiverId) {
                    rates = rates.filter(rate => {
                        return [this.filterByCaregiverId, null].includes(rate.caregiver_id)
                    });
                    filtered = true;
                }

                if (filtered) {
                    // Sort by most specific first
                    rates = rates.sort((rateA, rateB) => {
                        rateA.specificity = RateFactory.getRateSpecificity(rateA); // debug
                        rateB.specificity = RateFactory.getRateSpecificity(rateB); // debug
                        return RateFactory.getRateSpecificity(rateA) - RateFactory.getRateSpecificity(rateB);
                    }).reverse();
                }

                return rates;
            },
        },

        methods: {

            addRateWizard(defaultRate=false) {
                this.addNewCaregiver = false;
                this.defaultRateOnWizard = defaultRate;
                this.rateWizardModal = true;
            },

            addRate(rateObject={}) {
                this.addNewCaregiver = false;
                this.items.push({
                    caregiver_name: this.getCaregiverName(rateObject.caregiver_id),
                    service_id: rateObject.service_id || null,
                    payer_id: rateObject.payer_id || null,
                    caregiver_id: rateObject.caregiver_id ||null,
                    effective_start: rateObject.effective_start || moment().format('MM/DD/YYYY'),
                    effective_end: rateObject.effective_end || moment('9999-12-31').format('MM/DD/YYYY'),
                    caregiver_hourly_rate: rateObject.caregiver_hourly_rate || '0.00',
                    caregiver_fixed_rate: rateObject.caregiver_fixed_rate || '0.00',
                    client_hourly_rate: rateObject.client_hourly_rate || '0.00',
                    client_fixed_rate: rateObject.client_fixed_rate || '0.00',
                });

                console.log(rateObject);
                if (rateObject.service_id !== undefined) {
                    // If a rate object is passed, attempt to save the rate structure
                    this.saveRates();
                    this.defaultRateOnWizard = false;
                }

                // Scroll to bottom of table
                $('html, body').animate({
                    scrollTop: $('#save-rates').offset().top
                }, 500, 'linear');
            },

            async removeRate(item) {
                this.busyRemoving = item.id;

                // If there is only one rate entry for the caregiver, check if
                // they can be unassigned here and block the action if there
                // is a conflict.
                let caregiversRates = this.items.filter(x => x.caregiver_id === item.caregiver_id).length;
                if (caregiversRates === 1) {
                    let response = await axios.get(`/business/clients/${this.client.id}/can-unassign/${item.caregiver_id}`);
                    if (response.data.error) {
                        alert(response.data.error);
                        this.busyRemoving = null;
                        return;
                    }
                }

                if (confirm('Are you sure you wish to remove this rate line?  You\'ll still need to save your changes afterwards.')) {
                    this.items = this.items.filter(x => { return JSON.stringify(x) !== JSON.stringify(item) });
                }
                this.busyRemoving = null;
            },

            async saveRates() {
                let form = new Form({
                    rates: this.items,
                });
                form.patch(`/business/clients/${this.client.id}/rates`)
                    .then( async ({ data }) => {
                        await this.fetchAssignedCaregivers();
                        this.setItems(data.data);
                    })
                    .catch(e => {
                        this.fetchAssignedCaregivers();
                    })
                    .finally(() => {
                        this.fetchOtherCaregivers();
                    })
            },

            addCaregiver() {
                this.addNewCaregiver = true;
                this.rateWizardModal = true;
                // this.caregiverForm = new Form({
                //     caregiver_id: null,
                // });
                // this.clientCaregiverModal = true;
            },

            // async saveCaregiver() {
            //     await this.caregiverForm.post('/business/clients/' + this.client.id + '/caregivers');
            //     this.fetchAssignedCaregivers();
            //     this.fetchOtherCaregivers();
            //     this.clientCaregiverModal = false;
            // },

            // async removeAssignedCaregiver(caregiver_id) {
            //     if (confirm('Are you sure you wish to remove this caregiver from this client?')) {
            //         let form = new Form({caregiver_id: caregiver_id});
            //         await form.post('/business/clients/'+this.client.id+'/detach-caregiver');
            //         this.fetchAssignedCaregivers();
            //         this.fetchOtherCaregivers();
            //     }
            // },

            async excludeCaregiver() {
                if (this.excludeForm.id) {
                    const response = await this.excludeForm.patch('/business/clients/'+this.client.id+'/exclude-caregiver/'+this.excludeForm.id);
                } else {
                    const response = await this.excludeForm.post('/business/clients/'+this.client.id+'/exclude-caregiver');
                }
                this.fetchExcludedCaregivers();
                this.fetchOtherCaregivers();
                this.excludeForm = new Form({
                    id: "",
                    caregiver_name: "",
                    caregiver_id: "",
                    note: "",
                    reason: '',
                    effective_at: moment().format('MM/DD/YYYY'),
                });
                this.clientExcludeCaregiverModal = false;
            },

            removeExcludedCaregiver(id) {
                if (confirm('Are you sure you want to re-include this caregiver for this client?')) {
                    axios.delete('/business/clients/excluded-caregiver/'+id)
                        .then(response => {
                            this.fetchExcludedCaregivers();
                            this.fetchOtherCaregivers();
                        }).catch(error => {
                        console.error(error.response);
                    });
                }
            },

            editExcludedCaregiver(item) {
                this.excludeForm = new Form({
                    id: item.id,
                    caregiver_name: item.caregiver.name,
                    caregiver_id: item.caregiver_id,
                    note: item.note,
                    reason: item.reason,
                    effective_at: moment(item.effective_at).format('MM/DD/YYYY'),
                });
                this.clientExcludeCaregiverModal = true
            },

            setItems(data) {
                if (data) {
                    this.items = data.map(x => {
                        x.caregiver_name = this.getCaregiverName(x.caregiver_id);
                        x.caregiver_hourly_rate = parseFloat(x.caregiver_hourly_rate).toFixed(2);
                        x.caregiver_fixed_rate = parseFloat(x.caregiver_fixed_rate).toFixed(2);
                        x.client_hourly_rate = parseFloat(x.client_hourly_rate).toFixed(2);
                        x.client_fixed_rate = parseFloat(x.client_fixed_rate).toFixed(2);
                        x.effective_start = moment(x.effective_start).format('MM/DD/YYYY');
                        x.effective_end = moment(x.effective_end).format('MM/DD/YYYY');
                        return x;
                    });
                } else {
                    this.items = [];
                }
            },

            async fetchAssignedCaregivers() {
                let response = await axios.get('/business/clients/' + this.client.id + '/caregivers')
                if (Array.isArray(response.data)) {
                    this.caregivers = response.data;
                } else {
                    this.caregivers = [];
                }
            },

            async fetchExcludedCaregivers() {
                const response = await axios.get('/business/clients/' + this.client.id + '/excluded-caregivers');
                if (Array.isArray(response.data)) {
                    this.excludedCaregivers = response.data;
                }
            },

            async fetchOtherCaregivers() {
                const response = await axios.get('/business/clients/' + this.client.id + '/potential-caregivers');
                if (Array.isArray(response.data)) {
                    this.otherCaregivers = response.data;
                }
            },

            async fetchPayers() {
                let response = await axios.get('/business/payers?json=1');
                if (Array.isArray(response.data)) {
                    this.payers = response.data;
                } else {
                    this.payers = [];
                }
            },

            async fetchServices() {
                let response = await axios.get('/business/services?json=1');
                if (Array.isArray(response.data)) {
                    this.services = response.data;
                } else {
                    this.services = [];
                }
            },

            getAllyFee(clientRate) {
                let computed = (clientRate) * this.allyRate;
                return computed.toFixed(2);
            },

            getProviderFee(clientRate, caregiverRate) {
                let allyFee = this.getAllyFee(clientRate);
                let computed = clientRate - caregiverRate - allyFee;
                return computed.toFixed(2);
            },

            onChangePayer(e, item) {
                // automatically load the rates for the business and use
                // them as the default values when the payer is changed.
                this.setDefaultRates(item, e, item.service_id);
            },

            onChangeService(e, item) {
                // automatically load the rates for the business and use
                // them as the default values when the service is changed.
                this.setDefaultRates(item, item.payer_id, e);
            },

            setDefaultRates(item, payer_id, service_id) {
                let payer = this.payers.find(x => x.id == payer_id);
                
                if (! payer) {
                    // no matching rate for payer / service
                    console.log('no payer match for the business');
                    return;
                }

                let rate = payer.rates.find(x => {
                    // filter rate by effective dates so if there are multiple entries for
                    // a payer/service combo, it will pull the active one.
                    return x.service_id == service_id &&
                        moment().isBetween(x.effective_start, x.effective_end)
                });

                if (rate) {
                    item.client_hourly_rate = rate.hourly_rate;
                    item.client_fixed_rate = rate.fixed_rate;
                    console.log('payer service rates set', rate);
                    return;
                }

                console.log('no matching rate for payer '+payer_id+' / service '+service_id);

                rate = payer.rates.find(x => {
                    // pull the default rate for the payer (if one exists)
                    return x.service_id == null &&
                        moment().isBetween(x.effective_start, x.effective_end)
                });
                if (rate) {
                    item.client_hourly_rate = rate.hourly_rate;
                    item.client_fixed_rate = rate.fixed_rate;
                    console.log('payer default rates set', rate);
                    return;
                }

                console.log('no default rate for payer '+payer_id);
            },

            getCaregiverName(id) {
                let cg = this.caregivers.find(x => x.id === id);
                if (cg) {
                    return cg.name;
                }

                return '(All)';
            },
        },

        async mounted() {
            await this.fetchAssignedCaregivers();
            this.fetchExcludedCaregivers();
            this.fetchServices();
            this.fetchPayers();
            this.fetchOtherCaregivers();
            this.setItems(this.rates);
        },
    }
</script>

<style scoped>
    .caregiver-list-table th, .caregiver-list-table td {
        padding: 0.5rem 0.75rem;
    }
</style>
