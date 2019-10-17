<template>
    <b-card
            header="Caregivers &amp; Rates"
            header-text-variant="white"
            header-bg-variant="info"
    >
        <div class="d-flex">
            <div class="mb-3">
                <h5><strong>Referred Caregivers and Rates</strong></h5>
                <small>Any caregiver listed below has the ability to clock in and out for this client, independent of a schedule.</small>
            </div>
            <div class="ml-auto">
                {{ paymentText }}
            </div>
        </div>
        <div class="mb-3">
            <b-btn variant="info" @click="addCaregiver()">Add Caregiver</b-btn>
            <b-btn variant="primary" @click="addRateWizard()">Add Rate to Existing Caregiver</b-btn>
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

        <div class="table-responsive client-rate-table">
            <table class="table-fit-more table b-table table-striped table-hover table-bordered">
                <thead>
                    <tr>
                        <th scope="col" colspan="5"></th>
                        <th scope="col" colspan="4" class="bl bt br text-center p-0">Hourly Rate</th>
                        <th scope="col" colspan="4" class="bt br text-center p-0">Fixed / Daily Rate</th>
                        <th scope="col" colspan="1"></th>
                    </tr>
                    <tr>
                        <th scope="col">Caregiver</th>
                        <th scope="col">Service</th>
                        <th scope="col">Payer</th>
                        <th scope="col">Effective Start</th>
                        <th scope="col">Effective End</th>
                        <th scope="col" class="bl">Caregiver</th>
                        <th scope="col">Registry</th>
                        <th scope="col">Ally</th>
                        <th scope="col" class="br">Total</th>
                        <th scope="col">Caregiver</th>
                        <th scope="col">Registry</th>
                        <th scope="col">Ally</th>
                        <th scope="col" class="br">Total</th>
                        <th scope="col"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="item in filteredItems" :key="item.id">
                        <!-- Caregiver -->
                        <td scope="row">
                            {{ item.caregiver_name }}&nbsp;
                            <a href="#" v-b-popover.hover="caregiverInfo(item)" title="Caregiver Stats">
                                <i class="fa fa-info-circle" style="color: #1e88e5" size="sm"></i>
                            </a>
                        </td>
                        <!-- Service -->
                        <td>
                            <b-select v-model="item.service_id" size="sm" @change="(e) => onChangeService(e, item)">
                                <option :value="null">(All)</option>
                                <option v-for="service in services" :value="service.id" :key="service.id">
                                    {{ service.name }}<template v-if="service.code"> : {{ service.code }}  </template>
                                </option>
                            </b-select>
                        </td>
                        <!-- Payer -->
                        <td>
                            <b-select v-model="item.payer_id" size="sm" @change="(e) => onChangePayer(e, item)">
                                <option :value="null">(All)</option>
                                <option :value="0">({{ client.name }})</option>
                                <option v-for="item in payers" :value="item.id" :key="item.id">{{ item.name }}</option>
                            </b-select>
                        </td>
                        <!-- Effective Start -->
                        <td>
                            <mask-input type="date"
                                v-model="item.effective_start"
                                class="date-input form-control-sm"
                            ></mask-input>
                        </td>
                        <!-- Effective End -->
                        <td class="br">
                            <mask-input type="date"
                                v-model="item.effective_end"
                                class="date-input form-control-sm"
                            ></mask-input>
                        </td>
                        <!-- Caregiver Hourly Rate -->
                        <td class="bl">
                            <b-form-input name="caregiver_hourly_rate"
                                class="money-input"
                                type="number"
                                step="0.01"
                                min="0"
                                max="999.99"
                                required
                                v-model="item.caregiver_hourly_rate"
                                @change="updateProviderRates(item)"
                                size="sm"
                            ></b-form-input>
                        </td>
                        <!-- Provider Hourly Rate -->
                        <td>
                            <b-form-input name="provider_hourly_rate"
                                class="money-input"
                                type="number"
                                step="0.01"
                                min="0"
                                max="999.99"
                                required
                                v-model="item.provider_hourly_rate"
                                @change="updateClientRates(item)"
                                size="sm"
                            ></b-form-input>
                        </td>
                        <!-- Ally Hourly Fee -->
                        <td>
                            {{ getAllyFee(item.client_hourly_rate) }}
                        </td>
                        <!-- Total Hourly Rate -->
                        <td class="br">
                            <b-form-input name="client_hourly_rate"
                                class="money-input"
                                type="number"
                                step="0.01"
                                min="0"
                                max="999.99"
                                required
                                v-model="item.client_hourly_rate"
                                @change="updateProviderRates(item)"
                                size="sm"
                            ></b-form-input>
                        </td>
                        <!-- Caregiver Fixed Rate -->
                        <td>
                            <b-form-input name="caregiver_fixed_rate"
                                class="money-input"
                                type="number"
                                step="0.01"
                                min="0"
                                max="999.99"
                                required
                                v-model="item.caregiver_fixed_rate"
                                @change="updateProviderRates(item)"
                                size="sm"
                            ></b-form-input>
                        </td>
                        <!-- Provider Fixed Rate -->
                        <td>
                            <b-form-input name="provider_fixed_rate"
                                class="money-input"
                                type="number"
                                step="0.01"
                                min="0"
                                max="999.99"
                                required
                                v-model="item.provider_fixed_rate"
                                @change="updateClientRates(item)"
                                size="sm"
                            ></b-form-input>
                        </td>
                        <!-- Ally Fixed Fee -->
                        <td>
                            {{ getAllyFee(item.client_fixed_rate) }}
                        </td>
                        <!-- Total Fixed Rate -->
                        <td class="br">
                            <b-form-input name="client_fixed_rate"
                                class="money-input"
                                type="number"
                                step="0.01"
                                min="0"
                                max="999.99"
                                required
                                v-model="item.client_fixed_rate"
                                @change="updateProviderRates(item)"
                                size="sm"
                            ></b-form-input>
                        </td>
                        <!-- Actions -->
                        <td class="hidden-print">
                            <b-btn size="sm" @click="removeRate(item)" :disabled="busyRemoving === item.id">
                                <i v-if="busyRemoving === item.id" class="fa fa-spinner fa-spin"></i>
                                <i v-else class="fa fa-trash"></i>
                            </b-btn>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="text-right">
            <b-btn id="save-rates" @click="saveRates()" variant="success">Save Rates</b-btn>
        </div>

        <hr/>
        <div class="mt-4">
            <h5>
                <strong>Excluded Caregivers </strong>
            </h5>
        </div>
        <div class="mb-3">
            <b-btn variant="info" @click="openExcludeModal()">Exclude Caregiver</b-btn>
        </div>
        <div class="table-responsive">
            <b-table bordered striped hover show-empty
                     :items="excludedCaregivers"
                     :fields="excludedFields"
                     empty-text="There are no excluded caregivers for this client."
            >
                <template slot="actions" scope="data">
                    <b-btn @click="editExcludedCaregiver(data.item)" size="sm" variant="info"><i class="fa fa-edit"></i>
                    </b-btn>
                    <b-btn @click="removeExcludedCaregiver(data.item.id)" size="sm" variant="danger"><i
                            class="fa fa-times"></i></b-btn>
                </template>
            </b-table>
        </div>

        <!-- MODALS -->
        <b-modal :title="excludeForm.id ? 'Update Excluded Caregiver' : 'Exclude Caregiver'"
                 v-model="clientExcludeCaregiverModal">
            <b-container fluid>
                <b-row>
                    <b-col lg="12">
                        <div class="toggleInactiveCaregivers">
                            <b-form-checkbox type="checkbox"
                                             v-model="displayAllCaregivers"
                                             value="1"
                                             unchecked-value="0"
                            >
                                Show Inactive Caregivers
                            </b-form-checkbox>
                        </div>
                        <b-form-group label="Caregiver *" label-for="exclude_caregiver_id">
                            <div v-if="excludeForm.id">
                                {{ excludeForm.caregiver_name }}
                            </div>
                            <b-form-select v-else name="exclude_caregiver_id" v-model="excludeForm.caregiver_id">
                                <option value="">--Select a Caregiver--</option>
                                <option v-for="item in otherCaregivers"
                                        :value="item.id"
                                        :key="item.id"
                                        v-if="item.active || displayAllCaregivers == 1"
                                >{{ item.name }}
                                </option>
                            </b-form-select>
                        </b-form-group>
                        <b-form-group label="Reason" label-for="exclude_reason">
                            <b-form-select name="exclude_reason" v-model="excludeForm.reason">
                                <option v-for="(item, index) in exclusionReasons" :value="index" :key="index">{{ item
                                    }}
                                </option>
                            </b-form-select>
                        </b-form-group>
                        <b-form-group label="Note" label-for="note">
                            <b-form-textarea v-model="excludeForm.note"
                                             :rows="3">
                            </b-form-textarea>
                        </b-form-group>
                        <b-form-group label="Effective Date" label-for="exclude_effective_at">
                            <mask-input v-model="excludeForm.effective_at" id="exclude_effective_at"
                                        type="date"></mask-input>
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

        <client-rate-wizard v-model="rateWizardModal"
                            :client="client"
                            :caregivers="caregivers"
                            :services="services"
                            :payers="payers"
                            :default-rate="defaultRateOnWizard"
                            :potential-caregivers="otherActiveCaregivers"
                            :add-mode="addNewCaregiver"
                            :ally-pct-original="allyRateOriginal"
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

        mixins: [FormatsDates],

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
                    reason: 'service_not_needed',
                    effective_at: moment().format('MM/DD/YYYY'),
                }),
                caregiverForm: new Form({caregiver_id: ""}),
                busyRemoving: null,
                displayAllCaregivers: 0,

                items: [],
                excludedFields: [
                    {key: 'caregiver_name', label: 'Name', sortable: true},
                    {
                        key: 'effective_at',
                        label: 'Date Excluded',
                        sortable: true,
                        formatter: x => this.formatDateFromUTC(x)
                    },
                    {
                        key: 'reason', label: 'Reason Code', sortable: true, formatter: x => {
                            return x ? this.exclusionReasons[x] : 'None'
                        }
                    },
                    {key: 'note', sortable: true},
                    {key: 'actions', label: '', class: 'hidden-print'},
                ],
                exclusionReasons: {
                    'quit': 'Caregiver quit',
                    'service_not_needed': 'Client no longer needs service',
                    'unhappy_client': 'Client not happy and refuses service from this caregiver',
                    'no_shows': 'Continual no shows',
                    'retired': 'Retired',
                },
            }
        },

        computed: {
            otherActiveCaregivers() {
                return this.otherCaregivers.filter(x => x.active);
            },

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
                let rates = this.items.map(item => {
                    this.updateProviderRates(item);
                    return item;
                });
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
            caregiverInfo(item) {
                let cg = this.caregivers.find(x => x.id === item.caregiver_id);
                if (! cg) {
                    cg = {}
                }
                const date = cg.last_service_date ? this.formatDateFromUTC(cg.last_service_date) : 'N/A';
                const hours = cg.total_hours ? cg.total_hours.toLocaleString() : 0;
                return `Last Service Date for this Client: ${date}\r\n\r\nTotal Hours Worked for this Client: ${hours}`
            },

            updateProviderRates(item) {
                item.provider_hourly_rate = RateFactory.getProviderFee(item.client_hourly_rate, item.caregiver_hourly_rate, this.allyRate).toFixed(2);
                item.provider_fixed_rate = RateFactory.getProviderFee(item.client_fixed_rate, item.caregiver_fixed_rate, this.allyRate).toFixed(2);
            },

            updateClientRates(item) {
                item.client_hourly_rate = RateFactory.getClientRate(item.provider_hourly_rate, item.caregiver_hourly_rate, this.allyRate).toFixed(2);
                item.client_fixed_rate = RateFactory.getClientRate(item.provider_fixed_rate, item.caregiver_fixed_rate, this.allyRate).toFixed(2);
            },

            openExcludeModal() {
                this.excludeForm = new Form({
                    id: "",
                    caregiver_name: "",
                    caregiver_id: "",
                    note: "",
                    reason: 'service_not_needed',
                    effective_at: moment().format('MM/DD/YYYY'),
                });
                this.clientExcludeCaregiverModal = true;
            },

            addRateWizard(defaultRate = false) {
                this.addNewCaregiver = false;
                this.defaultRateOnWizard = defaultRate;
                this.rateWizardModal = true;
            },

            addRate(rateObject = {}) {
                this.addNewCaregiver = false;
                this.items.push({
                    caregiver_name: this.getCaregiverName(rateObject.caregiver_id),
                    service_id: rateObject.service_id || null,
                    payer_id: rateObject.payer_id || rateObject.payer_id === 0 ? rateObject.payer_id : null,
                    caregiver_id: rateObject.caregiver_id || null,
                    effective_start: rateObject.effective_start || moment().subtract(1, 'week').format('MM/DD/YYYY'),
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
                    this.items = this.items.filter(x => {
                        return JSON.stringify(x) !== JSON.stringify(item)
                    });
                }
                this.busyRemoving = null;
            },

            async saveRates() {
                let form = new Form({
                    rates: this.items,
                });
                form.patch(`/business/clients/${this.client.id}/rates`)
                    .then(async ({data}) => {
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
                    const response = await this.excludeForm.patch('/business/clients/' + this.client.id + '/exclude-caregiver/' + this.excludeForm.id);
                } else {
                    const response = await this.excludeForm.post('/business/clients/' + this.client.id + '/exclude-caregiver');
                }
                this.fetchExcludedCaregivers();
                this.fetchOtherCaregivers();
                this.clientExcludeCaregiverModal = false;
            },

            removeExcludedCaregiver(id) {
                if (confirm('Are you sure you want to re-include this caregiver for this client?')) {
                    axios.delete('/business/clients/excluded-caregiver/' + id)
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
                let computed = RateFactory.getAllyFee(this.allyRate, clientRate);
                return computed.toFixed(2);
            },

            getProviderFee(clientRate, caregiverRate) {
                let computed = RateFactory.getProviderFee(clientRate, caregiverRate, this.allyRate);
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

                if (!payer) {
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

                console.log('no matching rate for payer ' + payer_id + ' / service ' + service_id);

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

                console.log('no default rate for payer ' + payer_id);
            },

            getCaregiverName(id) {
                let cg = this.caregivers.find(x => x.id === id);
                if (cg) {
                    return cg.firstname + ' ' + cg.lastname;
                }

                cg = this.otherCaregivers.find(x => x.id === id);
                if (cg) {
                    return cg.firstname + ' ' + cg.lastname;
                }

                return '(All)';
            },

            displayCaregivers(val){
                console.log("CHECKED");
                console.log(JSON.stringify(val));
            }

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
    .client-rate-table { font-size: 14px; }
    .caregiver-list-table th, .caregiver-list-table td {
        padding: 0.5rem 0.75rem;
    }
    .bt { border-top: 1px solid #A9A9A9!important; }
    .bl { border-left: 1px solid #A9A9A9!important; }
    .br { border-right: 1px solid #A9A9A9!important; }
    .bb { border-bottom: 1px solid #A9A9A9!important; }

    .toggleInactiveCaregivers{
        text-align: right;
        position: relative;
        top: 32px;
        left: 22px;
    }

    .toggleInactiveCaregivers span{
        position: relative;
        bottom: 5px;
    }
</style>
