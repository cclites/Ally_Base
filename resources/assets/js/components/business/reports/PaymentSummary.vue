<template>
    <div>
        <b-row>
            <b-col lg="12">
                <b-card
                        header="This report shows a summary of all payments made."
                        header-text-variant="white"
                        header-bg-variant="info"
                >
                    <b-row>
                        <business-location-form-group
                                v-model="form.businesses"
                                label="Office Location"
                                :allow-all="false"
                                class="mr-2"
                        />
                        <b-form-group label="Start Date" class="mb-2 mr-2">
                            <date-picker v-model="form.start_date" name="start_date"></date-picker>
                        </b-form-group>
                        <b-form-group label="End Date" class="mb-2 mr-2">
                            <date-picker v-model="form.end_date" name="end_date"></date-picker>
                        </b-form-group>
                        <b-form-group label="Client Type" class="mb-2 mr-2">
                            <b-form-select v-model="form.client_type">
                                <option value="">All</option>
                                <option v-for="item in clientTypeOptions" :value="item.value">{{ item.text }}</option>
                            </b-form-select>
                        </b-form-group>
                        <b-form-group label="Clients" class="mb-2 mr-2">
                            <b-select v-model="form.client">
                                <option value="">All Clients</option>
                                <option v-for="client in clients" :key="client.id" :value="client.id">{{ client.name }}</option>
                            </b-select>
                        </b-form-group>
                        <b-form-group label="Payment Type" class="mb-2 mr-2">
                            <b-form-select v-model="form.payment_method">
                                <option value="">All</option>
                                <option v-for="item in paymentMethodTypeOptions" :value="item.value">{{ item.text }}</option>
                            </b-form-select>
                        </b-form-group>
                        <b-col md="2">
                            <b-form-group label="&nbsp;">
                                <b-button-group>
                                    <b-button @click="fetch()" variant="info" :disabled="form.busy"><i class="fa fa-file-pdf-o mr-1"></i>Generate Report</b-button>
                                    <b-button @click="printReport()"><i class="fa fa-print mr-1"></i>Print</b-button>
                                </b-button-group>
                            </b-form-group>
                        </b-col>

                    </b-row>

                    <loading-card v-show="form.busy"></loading-card>

                    <div v-show="!form.busy">
                        <div class="table-responsive" >
                                <b-table
                                        class="payers-summary-table"
                                        :items="items"
                                        :fields="fields"
                                        :sort-by="form.payer"
                                        :busy="form.busy"
                                        :current-page="currentPage"
                                        :per-page="perPage"
                                        :footClone="footClone"
                                        :show-empty="true"
                                >
                                    <template slot="invoice" scope="row">
                                        <a :href="invoiceUrl(row.item.invoice_id)" target="_blank">{{ row.item.invoice }}</a>
                                    </template>

                                    <template slot="client_name" scope="row">
                                        <a :href="`/business/clients/${row.item.client_id}`">{{ row.item.client_name }}</a>
                                    </template>

                                    <template slot="FOOT_client_type" scope="item" class="primary">&nbsp;
                                    </template>

                                    <template slot="FOOT_payment_type" scope="item" class="primary">&nbsp;
                                    </template>

                                    <template slot="FOOT_client_name" scope="item" class="primary">
                                    </template>

                                    <template slot="FOOT_date" scope="item">
                                    </template>

                                    <template slot="FOOT_invoice" scope="item" class="primary">&nbsp;
                                    </template>

                                    <template slot="FOOT_amount" scope="item" class="primary">
                                        &nbsp;<strong>Total Invoiced Amount: </strong> {{ moneyFormat(totals.amount ) }}
                                    </template>

                                    <template slot="FOOT_caregiver_amount" scope="item" class="primary">
                                        &nbsp;<strong>Total Caregivers Amount: </strong> {{ moneyFormat(totals.caregiver_amount) }}
                                    </template>

                                    <template slot="FOOT_registry_amount" scope="item" class="primary">
                                        &nbsp;<strong>Total Registry Amount: </strong> {{ moneyFormat(totals.registry_amount ) }}
                                    </template>

                                </b-table>
                        </div>
                    </div>

                    <b-row v-if="!form.busy && items.length > 0">
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
    import FormatsStrings from "../../../mixins/FormatsStrings";
    import FormatsDates from "../../../mixins/FormatsDates";
    import Constants from "../../../mixins/Constants";
    import {mapGetters} from "vuex";

    export default {
        name: "PaymentSummaryByPayer",
        components: {BusinessLocationFormGroup, BusinessLocationSelect},
        mixins: [FormatsDates, FormatsNumbers, Constants, FormatsStrings],

        data() {
            return {
                form: new Form({
                    businesses: '',
                    start_date: moment().startOf('isoweek').subtract(7, 'days').format('MM/DD/YYYY'),
                    end_date: moment().startOf('isoweek').subtract(1, 'days').format('MM/DD/YYYY'),
                    client_type: '',
                    client: '',
                    payment_method: '',
                    json: 1
                }),
                totalRows: 0,
                perPage: 100,
                currentPage: 1,
                sortBy: 'client_name',
                sortDesc: false,
                fields: [
                    {key: 'client_name', label: 'Client', sortable: true },
                    {key: 'client_type', label: 'Client Type', sortable: true, formatter: x => this.resolveOption(x, this.clientTypes) },
                    {key: 'payment_type', label: 'Payment Method', sortable: true, formatter: x => this.resolveOption(x, this.paymentMethodTypeOptions) },
                    {key: 'date', label: 'Invoice Date', sortable: true, formatter: x => { return this.formatDateFromUTC(x) }},
                    {key: 'invoice', label: 'Invoice', sortable: true },
                    {key: 'amount', label: 'Total Invoiced Amount', sortable: true, formatter: x => { return this.moneyFormat(x)}},
                    {key: 'caregiver_amount', label: 'Caregiver Amount', sortable: true, formatter: x => { return this.moneyFormat(x)}},
                    {key: 'registry_amount', label: 'Registry Amount', sortable: true, formatter: x => { return this.moneyFormat(x)}},
                ],
                items: [],
                item: '',
                totals: [],
                payers: [],
                footClone: false,
            }
        },
        methods: {
            fetch() {
                this.form.get('/business/reports/payment-summary-by-payer')
                    .then( ({ data }) => {
                        this.items = data.results;
                        this.totals = data.totals;
                        this.totalRows = this.items.length;
                    })
                    .catch(e => {})
                    .finally(() => {
                        this.footClone = true;
                    })
            },

            printReport(){
                $(".payers-summary-table").print();
            },

            invoiceUrl(invoice, view="") {
                return `/business/client/invoices/${invoice}/${view}`;
            }

        },

        async mounted() {
            await this.$store.dispatch('filters/fetchResources', ['clients']);
        },

        computed: {
            ...mapGetters({
                clientList: 'filters/clientList',
            }),

            clients() {
                // if (this.showInactiveClients) {
                    return this.clientList;
                // }

                // return this.clientList.filter(x => x.active == 1);
            },

            caregiversTotal(){
                return this.items.reduce(function(sum, item){
                   return sum + item.caregivers_amount;
                }, 0);
            },

            registryTotal(){
                return this.items.reduce(function(sum, item){
                    return sum + item.registry_amount;
                }, 0);
            },
        },

        watch: {
            'form.businesses'(newValue, oldValue) {
                this.$store.commit('filters/setBusiness', newValue);
            }
        },
        
    }
</script>

<style>

     .payers-summary-table tfoot th{
         padding-top: 40px !important;
     }
</style>