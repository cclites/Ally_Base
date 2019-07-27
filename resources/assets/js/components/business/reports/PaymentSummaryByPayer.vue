<template>
    <div>
        <b-row>
            <b-col lg="12">
                <b-card
                        header="Filters"
                        header-text-variant="white"
                        header-bg-variant="info"
                >
                    <b-row>
                        <business-location-form-group
                                v-model="form.business"
                                label="Office Location"
                                :allow-all="false"
                                class="mr-2"
                        />
                        <b-form-group label="Start Date" class="mb-2 mr-2">
                            <date-picker v-model="form.start" name="start_date"></date-picker>
                        </b-form-group>
                        <b-form-group label="End Date" class="mb-2 mr-2">
                            <date-picker v-model="form.end" name="end_date"></date-picker>
                        </b-form-group>
                        <b-form-group label="Client Type" class="mb-2 mr-2">
                            <b-form-select v-model="form.client_type" name="client_type">
                                <option value="null">All</option>
                                <option v-for="item in clientTypes" :key="item.value" :value="item.value">
                                    {{ item.text }}
                                </option>
                            </b-form-select>
                        </b-form-group>

                        <b-form-group label="Clients" class="mb-2 mr-2">
                            <b-select v-model="form.client" class="mb-2 mr-2">
                                <option value="">All Clients</option>
                                <option v-for="client in clients" :key="client.id" :value="client.id">{{ client.nameLastFirst }}</option>
                            </b-select>
                        </b-form-group>

                        <b-form-group label="Payers" class="mb-2 mr-2">
                            <b-form-select v-model="form.payer" class="mb-2 mr-2" name="payer">
                                <option value="">All Payers</option>
                                <option :value="PRIVATE_PAY_ID">PRIVATE PAY</option>
                                <option :value="OFFLINE_PAY_ID">OFFLINE</option>
                                <option v-for="p in payers" :key="p.id" :value="p.id">{{ p.name }}</option>
                            </b-form-select>
                        </b-form-group>

                        <b-col md="2">
                            <b-form-group label="&nbsp;">
                                <b-button-group>
                                    <b-button @click="fetch()" variant="info" :disabled="busy"><i class="fa fa-file-pdf-o mr-1"></i>Generate Report</b-button>
                                    <b-button @click="print()"><i class="fa fa-print mr-1"></i>Print</b-button>
                                </b-button-group>
                            </b-form-group>
                        </b-col>

                    </b-row>

                    <div class="d-flex justify-content-center" v-if="busy">
                        <div class="my-5">
                            <i class="fa fa-spinner fa-spin fa-3x fa-fw"></i>
                        </div>
                    </div>
                    <div v-else>
                        <b-row>
                            <b-col>
                                <b-table
                                        class="payers-summary-table"
                                        :items="items"
                                        :fields="fields"
                                        sort-by="payer"
                                        empty-text="No Results"
                                        :busy="busy"
                                        :current-page="currentPage"
                                        :per-page="perPage"
                                        :footClone="footClone"
                                >
                                    <template slot="FOOT_client_name" scope="item" class="primary">
                                        <strong>For Client: </strong>{{totals.client_name}}
                                    </template>

                                    <template slot="FOOT_date" scope="item">
                                        <strong>For Location: </strong> {{ totals.location }}
                                    </template>

                                    <template slot="FOOT_client_type" scope="item" class="primary">
                                        <strong>For Client Types: </strong> {{totals.client_type}}
                                    </template>

                                    <template slot="FOOT_payer" scope="item">
                                        <strong>For Payer: </strong> {{totals.payer}}
                                    </template>


                                    <template slot="FOOT_amount" scope="item" class="primary">
                                        &nbsp;<strong>Total:  </strong> {{ moneyFormat(totals.total ) }}
                                    </template>
                                </b-table>
                            </b-col>
                        </b-row>
                    </div>

                    <b-row v-if="this.items.length > 0">
                        <b-col lg="6" >
                            <b-pagination :total-rows="totalRows" :per-page="perPage" v-model="currentPage" />
                        </b-col>
                        <b-col lg="6" class="text-right">
                            Showing {{ perPage < totalRows ? perPage : totalRows }} of {{ totalRows }} results
                        </b-col>
                    </b-row>


                </b-card>
            </b-col>
        </b-row>
    </div>
</template>

<script>

    import BusinessLocationSelect from "../../business/BusinessLocationSelect";
    import BusinessLocationFormGroup from "../../business/BusinessLocationFormGroup";
    import FormatsNumbers from "../../../mixins/FormatsNumbers";
    import FormatsDates from "../../../mixins/FormatsDates";
    import Constants from "../../../mixins/Constants";

    export default {
        name: "PaymentSummaryByPayer",
        components: {BusinessLocationFormGroup, BusinessLocationSelect},
        mixins: [FormatsDates, FormatsNumbers, Constants],
        data() {
            return {
                form: new Form({
                    business: '',
                    start: moment().startOf('isoweek').subtract(7, 'days').format('MM/DD/YYYY'),
                    end: moment().startOf('isoweek').subtract(1, 'days').format('MM/DD/YYYY'),
                    client_type: '',
                    client: '',
                    payer: '',
                    json: 1
                }),
                busy: false,
                totalRows: 0,
                perPage: 25,
                currentPage: 1,
                sortBy: 'payer',
                sortDesc: false,
                fields: [
                    {key: 'client_name', label: 'Client', sortable: true,},
                    {key: 'date', label: 'Invoice Date', sortable: true, formatter: x => { return this.formatDate(x) }},
                    {key: 'client_type', label: 'Client Type', sortable: true,},
                    {key: 'payer', label: 'Payer', sortable: true,},
                    {key: 'amount', label: 'Amount', sortable: true, formatter: x => { return this.moneyFormat(x) }},
                ],
                items: [],
                item: '',
                totals: [],
                payers: [],
                clients: [],
                footClone: false
            }
        },
        methods: {
            fetch() {
                this.loading = true;
                this.form.get('/business/reports/payment-summary-by-payer')
                    .then( ({ data }) => {
                        this.items = data.data;
                        this.totals = data.totals;
                        this.totalRows = this.items.length;
                    })
                    .catch(e => {})
                    .finally(() => {
                        this.loading = false;
                        this.footClone = true;
                    })
            },

            print(){
                $(".summary-table").print();
            },

            getClients(){
                axios.get('/business/clientDropdownResource?business=' + this.form.business)
                    .then( ({ data }) => {
                        this.clients = data;
                    })
                    .catch(e => {})
                    .finally(() => {
                    })
            },

            getPayers(){
                axios.get('/business/payerDropdownResource')
                    .then( ({ data }) => {
                        this.payers = data;
                    })
                    .catch(e => {})
                    .finally(() => {
                    })
            },
        },

        mounted() {
            this.getClients();
            this.getPayers();
        },
    }
</script>

<style scoped>

</style>