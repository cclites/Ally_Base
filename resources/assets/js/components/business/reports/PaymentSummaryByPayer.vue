<template>
    <div>
        <b-row>
            <b-col lg="12">
                <b-card
                        header="This report shows all payments made by private pay clients"
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
                        <b-form-group label="Start Date" class="mb-2 mr-2 col-md-2">
                            <date-picker v-model="form.start" name="start_date"></date-picker>
                        </b-form-group>
                        <b-form-group label="End Date" class="mb-2 mr-2 col-md-2">
                            <date-picker v-model="form.end" name="end_date"></date-picker>
                        </b-form-group>
                        <b-form-group label="Clients" class="mb-2 mr-2">
                            <b-select v-model="form.client" class="mb-2 mr-2">
                                <option value="">All Clients</option>
                                <option v-for="client in clients" :key="client.id" :value="client.id">{{ client.nameLastFirst }}</option>
                            </b-select>
                        </b-form-group>

                        <b-col md="2">
                            <b-form-group label="&nbsp;">
                                <b-button-group>
                                    <b-button @click="fetch()" variant="info" :disabled="busy"><i class="fa fa-file-pdf-o mr-1"></i>Generate Report</b-button>
                                    <b-button @click="printReport()"><i class="fa fa-print mr-1"></i>Print</b-button>
                                </b-button-group>
                            </b-form-group>
                        </b-col>

                    </b-row>

                    <loading-card v-show="busy"></loading-card>

                    <div v-show="!busy">
                        <div class="table-responsive" >
                                <b-table
                                        class="payers-summary-table"
                                        :items="items"
                                        :fields="fields"
                                        :sort-by="form.payer"
                                        :busy="busy"
                                        :current-page="currentPage"
                                        :per-page="perPage"
                                        :footClone="footClone"
                                        :show-empty="true"
                                >
                                    <template slot="invoice" scope="row">
                                        <a :href="invoiceUrl(row.item.invoice)" target="_blank">{{ row.item.invoice }}</a>
                                    </template>

                                    <template slot="FOOT_client_name" scope="item" class="primary">
                                        <strong>For Client: </strong>{{totals.client_name}}
                                    </template>

                                    <template slot="FOOT_date" scope="item">
                                        <strong>For Location: </strong> {{ totals.location }}
                                    </template>

                                    <template slot="FOOT_invoice" scope="item" class="primary">
                                        &nbsp;
                                    </template>

                                    <template slot="FOOT_amount" scope="item" class="primary">
                                        &nbsp;<strong>Total Invoiced Amount: </strong> {{ moneyFormat(totals.total ) }}
                                    </template>
                                </b-table>
                        </div>
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
                perPage: 100,
                currentPage: 1,
                sortBy: 'client_name',
                sortDesc: false,
                fields: [
                    {key: 'client_name', label: 'Client', sortable: true,},
                    {key: 'date', label: 'Invoice Date', sortable: true, formatter: x => { return this.formatDate(x) }},
                    {key: 'invoice', label: 'Invoice', sortable: true,},
                    //{key: 'client_type', label: 'Client Type', sortable: true,},
                    {key: 'amount', label: 'Total Invoiced Amount', sortable: true, formatter: x => { return this.moneyFormat(x)}},
                    //{key: 'registry_amount', label: 'Total Registry Amount', sortable: true,},
                ],
                items: [],
                item: '',
                totals: [],
                payers: [],
                clients: [],
                footClone: false,
                firstRun: true
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

            printReport(){
                window.location = this.form.toQueryString(`/business/reports/payment-summary-by-payer?print=true`);
            },

            getClients(){
                axios.get('/business/dropdown/clients?businesses=' + this.form.business)
                    .then( ({ data }) => {
                        this.clients = data;
                    })
                    .catch(e => {})
                    .finally(() => {
                    })
            },

            invoiceUrl(invoice, view="") {
                return `/business/client/invoices/${invoice}/${view}`;
            }

        },

        watch: {
            'form.business'(newVal, oldVal){

                if(this.firstRun){
                    this.firstRun = false;
                    return;
                }

                if(newVal !== oldVal){
                    this.getClients();
                }
            }
        },

        mounted() {
            this.getClients();
        },
        
    }
</script>

<style>

     .payers-summary-table tfoot th{
         padding-top: 40px !important;
     }
</style>