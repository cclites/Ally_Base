<template>
    <div>
        <b-row>
            <b-col lg="12">
                <b-card
                        header="Select Date Range &amp; Filters"
                        header-text-variant="white"
                        header-bg-variant="info"
                >
                    <b-row>
                        <b-col lg="2">
                            <b-form-group label="Start Date">
                                <date-picker
                                        class="mb-1"
                                        name="start_date"
                                        v-model="form.start_date"
                                        placeholder="Start Date">
                                </date-picker>
                            </b-form-group>
                        </b-col>

                        <b-col lg="2">
                            <b-form-group label="End Date">
                                <date-picker
                                        class="mb-1"
                                        v-model="form.end_date"
                                        name="end_date"
                                        placeholder="End Date">
                                </date-picker>
                            </b-form-group>
                        </b-col>

                        <b-col lg="2">
                            <b-form-group label="Client Type">
                                <b-form-select v-model="clientType" class="mr-1 mb-1" name="client_id">
                                    <option value="">All Client Types</option>
                                    <option value="LTCI">LTC Insurance</option>
                                    <option value="medicaid">Medicaid</option>
                                    <option value="private_pay">Private Pay</option>
                                </b-form-select>
                            </b-form-group>
                        </b-col>

                        <b-col lg="2">
                            <b-form-group label="Client">
                                <b-form-select v-model="form.client_id" class="mr-1 mb-1" name="client_id">
                                    <option v-if="clients.length === 0" selected>Loading..</option>
                                    <option v-else value="">Select a Client</option>
                                    <option v-for="item in clients" :value="item.id">{{ item.nameLastFirst }}
                                    </option>
                                </b-form-select>
                            </b-form-group>
                        </b-col>

                        <b-col lg="2">
                            <b-form-group label="&nbsp;">
                                <!--<b-button type="submit">Preview</b-button>-->
                                <b-button variant="info" @click="fetchPreview()">Generate</b-button>
                            </b-form-group>
                        </b-col>
                    </b-row>

                    <loading-card v-show="loading"></loading-card>

                    <template v-if="selectedClient && ! loading">
                        <b-row align-h="center">
                            <b-col lg="10">
                                <div v-if="selectedClient.ltci_name">
                                    {{ selectedClient.ltci_name }}
                                </div>
                                <div v-if="selectedClient.ltci_address && selectedClient.ltci_city && selectedClient.ltci_state && selectedClient.ltci_zip">
                                    {{ selectedClient.ltci_address }} {{ selectedClient.ltci_city }}, {{ selectedClient.ltci_state }} {{ selectedClient.ltci_zip }}
                                </div>
                            </b-col>
                        </b-row>
                        <b-row align-h="center">
                            <b-col lg="10">
                                <div class="d-flex justify-content-center">
                                    Policy #: {{ selectedClient.ltci_policy }}<br>
                                    Claim #: {{ selectedClient.ltci_claim }}
                                </div>
                            </b-col>
                        </b-row>
                        <b-row align-h="center">
                            <b-col lg="10">
                                <div class="d-flex justify-content-between">
                                    <div>Client Name: {{ selectedClient.name }}</div>
                                    <div v-if="selectedClient.addresses">
                                        Client Address: {{ selectedClient.addresses[0].address1 }}<br>
                                        {{ selectedClient.addresses[0].city }}, {{ selectedClient.addresses[0].state }}
                                        {{ selectedClient.addresses[0].zip }}
                                    </div>
                                </div>
                                <b-table :items="items" :fields="fields" foot-clone>
                                    <template slot="FOOT_date" slot-scope="data"></template>
                                    <template slot="FOOT_hourly_total" slot-scope="data"></template>
                                    <template slot="FOOT_hours" slot-scope="data"></template>
                                    <template slot="FOOT_total" slot-scope="data">
                                        <strong>Total: {{ summaryTotal }}</strong>
                                    </template>
                                </b-table>
                                <div class="d-flex justify-content-between">
                                    <a class="btn btn-info" :href="downloadClaimLink" target="_blank">
                                        Download Full Claim (PDF)
                                    </a>
                                    <a class="btn btn-success" :href="viewClaimLink" target="_blank">
                                        View Claim (HTML)
                                    </a>
                                </div>
                            </b-col>
                        </b-row>
                    </template>
                </b-card>
            </b-col>
        </b-row>
    </div>
</template>

<script>
    import FormatsNumbers from "../../../mixins/FormatsNumbers";
    import FormatsDates from "../../../mixins/FormatsDates";

    export default {
        mixins: [FormatsDates, FormatsNumbers],

        props: ['token'],

        data() {
            return {
                loadingClients: false,
                clients: [],
                preview: [],
                form: new Form({
                    start_date: moment().startOf('isoweek').format('MM/DD/YYYY'),
                    end_date: moment().startOf('isoweek').add(6, 'days').format('MM/DD/YYYY'),
                    caregiver_id: '',
                    client_id: '',
                    client_type: '',
                    export_type: 'html'
                }),
                clientType: 'LTCI',
                selectedClient: false,
                selectedItem: {},
                items: [],
                loading: false,
                fields: [
                    {
                        key: 'date',
                        formatter: (value) => { return this.formatDate(value) }
                    },
                    'hours',
                    {
                        key: 'hourly_total',
                        formatter: (value) => { return this.moneyFormat(value) }
                    },
                    {
                        key: 'total',
                        formatter: (value) => { return this.moneyFormat(value) }
                    }
                ]
            }
        },

        computed: {
            summaryTotal() {
                return this.moneyFormat(_.sumBy(this.items, 'total'));
            },

            viewClaimLink() {
                return '/business/reports/claims-report/print?client_id=' + this.selectedClient.id +
                    '&start_date=' + this.form.start_date +
                    '&end_date=' + this.form.end_date;
            },

            downloadClaimLink() {
                return this.viewClaimLink +
                    '&export_type=pdf';
            }
        },

        methods: {

            async loadClients() {
                this.clients = [];
                const response = await axios.get('/business/clients?json=1&client_type=' + this.clientType);
                this.clients = response.data;
            },

            fetchPreview() {
                this.loading = true;
                this.form.post('/business/reports/claims-report')
                    .then(response => {
                        this.items = response.data.summary;
                        this.selectedClient = response.data.client;
                        this.loading = false;
                    })
                    .catch(e => {
                        this.loading = false;
                    });
            },

            hoursType(item) {
                switch (item.hours_type) {
                    case 'default':
                        return 'Regular';
                    case 'overtime':
                        return 'OT';
                    case 'holiday':
                        return 'HOL';
                }
            }
        },

        watch: {
            clientType() {
                this.loadClients();
            }
        },

        created() {
            this.loadClients();
        }
    }
</script>

<style>
    table {
        font-size: 14px;
    }

    .table-info,
    .table-info > td,
    .table-info > th {
        font-weight: bold;
        font-size: 13px;
        background-color: #ecf7f9;
    }

    .table-sm td,
    .table-sm th {
        padding: 0.2rem 0;
    }

    .signature > svg {
        margin: -25px 0;
        width: 100%;
        height: auto;
        max-width: 400px;
    }
</style>