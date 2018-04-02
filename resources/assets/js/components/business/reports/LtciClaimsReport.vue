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
                        <b-col lg="3">
                            <b-form-group label="Start Date">
                                <date-picker
                                        class="mb-1"
                                        name="start_date"
                                        v-model="form.start_date"
                                        placeholder="Start Date">
                                </date-picker>
                            </b-form-group>
                        </b-col>
                        <b-col lg="3">
                            <b-form-group label="End Date">
                                <date-picker
                                        class="mb-1"
                                        v-model="form.end_date"
                                        name="end_date"
                                        placeholder="End Date">
                                </date-picker>
                            </b-form-group>
                        </b-col>
                        <b-col lg="3">
                            <b-form-group label="Caregiver">
                                <b-form-select v-model="form.caregiver_id" class="mx-1 mb-1" name="caregiver_id">
                                    <option value="">All Caregivers</option>
                                    <option v-for="item in caregiverList" :value="item.id">{{ item.nameLastFirst }}
                                    </option>
                                </b-form-select>
                            </b-form-group>
                        </b-col>
                        <b-col lg="3">
                            <b-form-group label="Client">
                                <b-form-select v-model="form.client_id" class="mr-1 mb-1" name="client_id">
                                    <option value="">Select a Client</option>
                                    <option v-for="item in clientList" :value="item.id">{{ item.nameLastFirst }}
                                    </option>
                                </b-form-select>
                            </b-form-group>
                        </b-col>
                        <!--<b-col lg="3">-->
                            <!--<b-form-group label="Client Type">-->
                                <!--<b-form-select v-model="form.client_type" class="mb-1" name="client_type">-->
                                    <!--<option value="">All</option>-->
                                    <!--<option value="private_pay">Private Pay</option>-->
                                    <!--<option value="LTCI">LTCI</option>-->
                                    <!--<option value="medicaid">MedicAid</option>-->
                                    <!--<option value="VA">VA</option>-->
                                <!--</b-form-select>-->
                            <!--</b-form-group>-->
                        <!--</b-col>-->
                        <b-col lg="3">
                            <b-form-group label="Export Type">
                                <b-form-radio-group id="export_type" v-model="form.export_type" name="export_type">
                                    <b-form-radio value="html">Online</b-form-radio>
                                    <b-form-radio value="pdf">PDF</b-form-radio>
                                </b-form-radio-group>
                            </b-form-group>
                        </b-col>
                        <b-col lg="3">
                            <b-form-group label="&nbsp;">
                                <!--<b-button type="submit">Preview</b-button>-->
                                <b-button variant="info" @click="fetchPreview()">Export</b-button>
                            </b-form-group>
                        </b-col>
                    </b-row>
                    <b-row v-if="selectedClient">
                        <b-col lg="12">
                            <h5>Claim Info</h5>
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
                            <div class="d-flex justify-content-around">
                                <b-btn variant="info">Print All Pages</b-btn>
                                <b-btn variant="info">Print Claim Invoice Page</b-btn>
                            </div>
                        </b-col>
                    </b-row>
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

        props: ['clients', 'caregivers', 'token'],

        data() {
            return {
                preview: [],
                form: new Form({
                    start_date: moment().startOf('isoweek').format('MM/DD/YYYY'),
                    end_date: moment().startOf('isoweek').add(6, 'days').format('MM/DD/YYYY'),
                    caregiver_id: '',
                    client_id: '',
                    client_type: '',
                    export_type: 'html'
                }),
                selectedClient: false,
                selectedItem: {},
                items: [],
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
            caregiverList() {
                return _.sortBy(this.caregivers, 'nameLastFirst');
            },

            clientList() {
                return _.sortBy(this.clients, 'nameLastFirst');
            },

            summaryTotal() {
                return this.moneyFormat(_.sumBy(this.items, 'total'));
            }
        },

        methods: {

            fetchPreview() {
                this.form.post('/business/reports/ltci-claims')
                    .then(response => {
                        this.items = response.data.summary;
                        this.selectedClient = response.data.client;
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