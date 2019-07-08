<template>
    <b-card :title="title">
        <div class="text-right">
            <b-btn variant="primary" @click="showSummary=!showSummary">{{ showSummary ? "Hide" : "Show" }} Summary</b-btn>
        </div>

        <b-row v-if="showSummary">
            <b-col lg="6">
                <table class="table table-bordered table-fit-more">
                    <tr>
                        <th>Client Summary</th>
                        <th>Client Total</th>
                        <th>CG Total</th>
                        <th>Ally Total</th>
                        <th>Reg Total</th>
                    </tr>
                    <tr v-for="item in clientSummary">
                        <td>{{ item.name }}</td>
                        <td>{{ numberFormat(item.client) }}</td>
                        <td>{{ numberFormat(item.caregiver) }}</td>
                        <td>{{ numberFormat(item.ally) }}</td>
                        <td>{{ numberFormat(item.provider) }}</td>
                    </tr>
                    <tr>
                        <th>Total</th>
                        <td>{{ numberFormat(clientSummaryTotal.client) }}</td>
                        <td>{{ numberFormat(clientSummaryTotal.caregiver) }}</td>
                        <td>{{ numberFormat(clientSummaryTotal.ally) }}</td>
                        <td>{{ numberFormat(clientSummaryTotal.provider) }}</td>
                    </tr>
                </table>
            </b-col>

            <b-col lg="6">
                <table class="table table-bordered table-fit-more">
                    <tr>
                        <th>Caregiver Summary</th>
                        <th>Client Total</th>
                        <th>CG Total</th>
                        <th>Ally Total</th>
                        <th>Reg Total</th>
                    </tr>
                    <tr v-for="item in caregiverSummary">
                        <td>{{ item.name }}</td>
                        <td>{{ numberFormat(item.client) }}</td>
                        <td>{{ numberFormat(item.caregiver) }}</td>
                        <td>{{ numberFormat(item.ally) }}</td>
                        <td>{{ numberFormat(item.provider) }}</td>
                    </tr>
                    <tr>
                        <th>Total</th>
                        <td>{{ numberFormat(caregiverSummaryTotal.client) }}</td>
                        <td>{{ numberFormat(caregiverSummaryTotal.caregiver) }}</td>
                        <td>{{ numberFormat(caregiverSummaryTotal.ally) }}</td>
                        <td>{{ numberFormat(caregiverSummaryTotal.provider) }}</td>
                    </tr>
                </table>
            </b-col>
        </b-row>

        <h4>Items</h4>

        <b-row>
            <b-col lg="8">
                <b-form inline>
                    <b-form-select v-model="clientId">
                        <option :value="null">All Clients</option>
                        <option v-for="client in clients" :value="client.id">{{ client.nameLastFirst }}</option>
                    </b-form-select>
                    <b-form-select v-model="caregiverId">
                        <option :value="null">All Caregivers</option>
                        <option v-for="caregiver in caregivers" :value="caregiver.id">{{ caregiver.nameLastFirst }}</option>
                    </b-form-select>
                    <business-location-form-group
                            v-model="business_name"
                            :allow-all="true"
                            :label="null"
                    />
                </b-form>
            </b-col>
            <b-col lg="4" class="text-right">
                <b-btn :href="`/business/statements/deposits/${deposit.id}/xls`" variant="success">Export to Excel</b-btn>
                <b-btn :href="`/business/statements/deposits/${deposit.id}/pdf`"><i class="fa fa-file-pdf-o"></i> PDF Statement</b-btn>
            </b-col>
        </b-row>


        <b-table bordered striped hover show-empty class="table-fit-more"
                 :items="filteredItems"
                 :fields="fields"
                 :sort-by.sync="sortBy"
                 :sort-desc.sync="sortDesc"
                 empty-text="No items are available"
        >
        </b-table>

    </b-card>

</template>

<script>
    import FormatsNumbers from "../../../mixins/FormatsNumbers";
    import FormatsDates from "../../../mixins/FormatsDates";
    import {Decimal} from 'decimal.js';
    import BusinessLocationSelect from '../../../components/business/BusinessLocationSelect';
    import BusinessLocationFormGroup from '../../../components/business/BusinessLocationFormGroup';

    export default {
        name: "ItemizedPayment",

        mixins: [FormatsNumbers, FormatsDates],
        components: { BusinessLocationFormGroup, BusinessLocationSelect },

        props: {
            deposit: {
                type: Object,
                required: true,
            },
            invoices: {
                type: Array,
                required: true,
            },
            items: {
                type: Array,
                required: true,
            }
        },

        computed: {
            filteredItems() {
                let filterFn = (item) => {

                    if (!this.caregiverId && !this.clientId) {
                        return true;
                    }
                    if (this.caregiverId && parseInt(item.caregiver.id) !== parseInt(this.caregiverId)) {
                        return false;
                    }
                    if (this.clientId && parseInt(item.client.id) !== parseInt(this.clientId)) {
                        return false;
                    }

                    if(parseInt(this.business_name) !== parseInt(item.business_id)){
                        return false;
                    }

                    return true;
                };

                return this.items.filter(filterFn).map(item => {
                    item.client_name = item.client ? item.client.nameLastFirst : "";
                    item.caregiver_name = item.caregiver ? item.caregiver.nameLastFirst : "";
                    item.caregiver_total = item.caregiver_rate * item.units;
                    item.client_total = item.client_rate * item.units;
                    item.ally_total = item.ally_rate * item.units;
                    return item;
                })
            },

            clientSummary() {
                this.clientSummaryTotal = {
                    ally: 0,
                    client: 0,
                    caregiver: 0,
                    provider: 0,
                };
                const summary = this.items.reduce((summary, item) => {
                    const clientId = item.client ? item.client.id : 0;
                    if (!summary[clientId]) {
                        summary[clientId] = {
                            name: item.client ? item.client.nameLastFirst : "Unknown",
                            ally: 0,
                            client: 0,
                            caregiver: 0,
                            provider: 0,
                        }
                    }
                    summary[clientId].ally += this.calcTotal(item.ally_rate, item.units);
                    this.clientSummaryTotal.ally += this.calcTotal(item.ally_rate, item.units);
                    summary[clientId].client += this.calcTotal(item.client_rate, item.units);
                    this.clientSummaryTotal.client += this.calcTotal(item.client_rate, item.units);
                    summary[clientId].caregiver += this.calcTotal(item.caregiver_rate, item.units);
                    this.clientSummaryTotal.caregiver += this.calcTotal(item.caregiver_rate, item.units);
                    summary[clientId].provider += this.calcTotal(item.rate, item.units);
                    this.clientSummaryTotal.provider += this.calcTotal(item.rate, item.units);
                    return summary;
                }, {});
                return Object.values(summary).sort((a, b) => a.name < b.name ? -1 : 1);
            },

            caregiverSummary() {
                this.caregiverSummaryTotal = {
                    ally: 0,
                    client: 0,
                    caregiver: 0,
                    provider: 0,
                };
                const summary = this.items.reduce((summary, item) => {
                    const caregiverId = item.caregiver ? item.caregiver.id : 0;
                    if (!summary[caregiverId]) {
                        summary[caregiverId] = {
                            name: item.caregiver ? item.caregiver.nameLastFirst : "Unknown",
                            ally: 0,
                            client: 0,
                            caregiver: 0,
                            provider: 0,
                        }
                    }
                    summary[caregiverId].ally += this.calcTotal(item.ally_rate, item.units);
                    this.caregiverSummaryTotal.ally += this.calcTotal(item.ally_rate, item.units);
                    summary[caregiverId].client += this.calcTotal(item.client_rate, item.units);
                    this.caregiverSummaryTotal.client += this.calcTotal(item.client_rate, item.units);
                    summary[caregiverId].caregiver += this.calcTotal(item.caregiver_rate, item.units);
                    this.caregiverSummaryTotal.caregiver += this.calcTotal(item.caregiver_rate, item.units);
                    summary[caregiverId].provider += this.calcTotal(item.rate, item.units);
                    this.caregiverSummaryTotal.provider += this.calcTotal(item.rate, item.units);
                    return summary;
                }, {});
                return Object.values(summary).sort((a, b) => a.name < b.name ? -1 : 1);
            },

            title() {
                let title = `Itemized View of Deposit #${this.deposit.id}. Deposit Amount: $${this.numberFormat(this.deposit.amount)}`;
                if (this.deposit.amount < 0) {
                    title += " (Withdrawal)";
                }
                return title;
            },
        },

        data() {
            return {
                caregivers: [],
                clients: [],
                business_name: null,
                business: null,
                clientId: null,
                caregiverId: null,
                clientSummaryTotal: {},
                caregiverSummaryTotal: {},
                showSummary: true,
                fields: [
                    {
                        key: "date",
                        formatter: val => val ? this.formatDateTime(val) : '-',
                        sortable: true,
                    },
                    {
                        key: "client_name",
                        label: "Client",
                        sortable: true,
                    },
                    {
                        key: "caregiver_name",
                        label: "Caregiver",
                        sortable: true,
                    },
                    {
                        key: "client_type",
                        label: "Client Type",
                        sortable: true,
                    },
                    {
                        key: "business_name",
                        label: "Business",
                        sortable: true,
                    },
                    /*
                    {
                        key: "payer",
                        label: "Payer",
                        sortable: true,
                    },
                     */
                    {
                        key: "name",
                        label: "Service Name",
                        sortable: true,
                    },
                    {
                        key: "units",
                        formatter: val => this.numberFormat(val),
                        sortable: true,
                    },
                    {
                        key: "client_rate",
                        formatter: val => this.numberFormat(val),
                        sortable: true,
                    },
                    {
                        key: "caregiver_rate",
                        formatter: val => this.numberFormat(val),
                        sortable: true,
                    },
                    {
                        key: "ally_rate",
                        formatter: val => this.numberFormat(val),
                        sortable: true,
                    },
                    {
                        key: "rate",
                        label: "Reg Rate",
                        formatter: val => this.numberFormat(val),
                        sortable: true,
                    },
                    {
                        key: "client_total",
                        label: "Client Total",
                        formatter: val => this.numberFormat(val),
                        sortable: true,
                    },
                    {
                        key: "caregiver_total",
                        label: "Caregiver Total",
                        formatter: val => this.numberFormat(val),
                        sortable: true,
                    },
                    {
                        key: "ally_total",
                        label: "Ally Total",
                        formatter: val => this.numberFormat(val),
                        sortable: true,
                    },
                    {
                        key: "total",
                        label: "Reg Total",
                        formatter: val => this.numberFormat(val),
                        sortable: true,
                    },
                ],
                sortBy: 'date',
                sortDesc: false,
            }
        },

        methods: {
            async loadClients() {
                const response = await axios.get("/business/clients?json=1");
                this.clients = response.data;
            },
            async loadCaregivers() {
                const response = await axios.get("/business/caregivers?json=1");
                this.caregivers = response.data;
            },
            calcTotal(rate, units) {
                return new Decimal(rate || 0).mul(parseFloat(units || 0)).toDecimalPlaces(2).toNumber();
            },
             getBusinessName($e){

             },
        },

        mounted() {
            this.loadClients();
            this.loadCaregivers();

            console.log(this.businesses);
        }
    }
</script>

<style scoped>
    .table-fit-more td {
        font-size: 12px;
    }
</style>