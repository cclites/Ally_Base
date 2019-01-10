<template>
    <b-card
        header="Client Rates"
        header-text-variant="white"
        header-bg-variant="info"
        >

        <div class="ml-auto mb-3">
            <b-btn variant="info" @click="add()">Add Rate</b-btn>
        </div>

        <div class="table-responsive mb-3">
            <b-table bordered striped hover show-empty
                     :items="items"
                     :fields="fields"
                     :current-page="currentPage"
                     :per-page="perPage"
                     :sort-by.sync="sortBy"
                     :sort-desc.sync="sortDesc"
                     ref="table"
            >
                <template slot="service_id" scope="row">
                    <b-select v-model="row.item.service_id" size="sm">
                        <option value="">(All)</option>
                        <option v-for="service in services" :value="service.id" :key="service.id">{{ service.name }}</option>
                    </b-select>
                </template>
                <template slot="payer_id" scope="row">
                    <b-select v-model="row.item.payer_id" size="sm">
                        <option value="">(All)</option>
                        <option v-for="item in payers" :value="item.id" :key="item.id">{{ item.name }}</option>
                    </b-select>
                </template>
                <template slot="caregiver_id" scope="row">
                    <b-select v-model="row.item.caregiver_id" size="sm">
                        <option value="">(All)</option>
                        <option v-for="item in caregivers" :value="item.id" :key="item.id">{{ item.name }}</option>
                    </b-select>
                </template>
                <template slot="effective_start" scope="row">
                    <mask-input v-model="row.item.effective_start" type="date" class="date-input"></mask-input>
                </template>
                <template slot="effective_end" scope="row">
                    <mask-input v-model="row.item.effective_end" type="date" class="date-input"></mask-input>
                </template>
                <template slot="caregiver_hourly_rate" scope="row">
                    <b-form-input name="caregiver_hourly_rate"
                        class="money-input"
                        type="number"
                        step="any"
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
                        step="any"
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
                        step="any"
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
                        step="any"
                        min="0"
                        max="999.99"
                        required
                        v-model="row.item.client_fixed_rate"
                        size="sm"
                    ></b-form-input>
                </template>
                <template slot="actions" scope="data">
                    <b-btn size="sm" @click="remove(data.index)">
                        <i class="fa fa-trash"></i>
                    </b-btn>
                </template>
            </b-table>

            <b-btn @click="save()" variant="success">Save Client Rates</b-btn>
        </div>
    </b-card>
</template>

<script>
    import FormatsDates from "../../mixins/FormatsDates";

    export default {
        props: {
            'client': {},
            'rates': Array,
        },

        mixins: [ FormatsDates ],

        data() {
            return {
                payers: [],
                caregivers: [],
                services: [],

                items: [],
                totalRows: 0,
                perPage: 30,
                currentPage: 1,
                sortBy: 'service.name',
                sortDesc: false,
                fields: [
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
                        key: 'caregiver_id',
                        label: 'Caregiver',
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
                        key: 'client_fixed_rate',
                        label: 'Client Fixed Rate',
                        sortable: true,
                    },
                    {
                        key: 'caregiver_fixed_rate',
                        label: 'CG Fixed Rate',
                        sortable: true,
                    },
                    {
                        key: 'actions',
                        class: 'hidden-print'
                    }
                ],
            }
        },

        computed: {
        },

        methods: {
            add() {
                this.items.push({
                    service_id: '',
                    payer_id: '',
                    caregiver_id: '',
                    effective_start: moment().format('MM/DD/YYYY'),
                    effective_end: moment('9999-12-31').format('MM/DD/YYYY'),
                    caregiver_hourly_rate: '0.00',
                    caregiver_fixed_rate: '0.00',
                    client_hourly_rate: '0.00',
                    client_fixed_rate: '0.00',
                })
            },

            remove(index) {
                if (index >= 0) {
                    this.items.splice(index, 1);
                }
            },

            setItems(data) {
                if (data) {
                    this.items = data.map(x => {
                        x.payer_id = x.payer_id ? x.payer_id : '',
                        x.service_id = x.service_id ? x.service_id : '',
                        x.caregiver_id = x.caregiver_id ? x.caregiver_id : '',
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

            save() {
                let form = new Form({
                    rates: this.items,
                    // rates: this.items.map(item => {
                    //     item.effective_start = moment(item.effective_start).format('YYYY-MM-DD');
                    //     item.effective_end = moment(item.effective_end).format('YYYY-MM-DD');
                    //     return item;
                    // })
                });
                console.log('submiting form: ', form);
                form.patch(`/business/clients/${this.client.id}/rates`)
                    .then( ({ data }) => {
                        this.setItems(data);
                    })
                    .catch(e => {
                    })
            },

            async fetchAssignedCaregivers() {
                let response = await axios.get('/business/clients/' + this.client.id + '/caregivers')
                if (Array.isArray(response.data)) {
                    this.caregivers = _.sortBy(response.data, ['lastname', 'firstname']);
                } else {
                    this.caregivers = [];
                }
            },
            
            async fetchPayers() {
                let response = await axios.get('/business/payers?json=1');
                if (Array.isArray(response.data)) {
                    this.payers = _.sortBy(response.data, ['lastname', 'firstname']);
                } else {
                    this.payers = [];
                }
            },

            async fetchServices() {
                let response = await axios.get('/business/service?json=1')
                if (Array.isArray(response.data)) {
                    this.services = response.data;
                } else {
                    this.services = [];
                }
            },
        },

        async mounted() {
            await this.fetchServices();
            await this.fetchPayers();
            await this.fetchAssignedCaregivers();
            this.setItems(this.rates);
        },
    }
</script>

<style scoped>
    .money-input { width: 85px!important }
    .date-input { max-width: 120px!important }
</style>
