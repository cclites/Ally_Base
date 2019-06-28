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
                        <th>Amount Due</th>
                    </tr>
                    <tr v-for="item in clientSummary">
                        <td>{{ item.name }}</td>
                        <td>{{ numberFormat(item.total) }}</td>
                    </tr>
                    <tr>
                        <th>Total</th>
                        <td>{{ numberFormat(clientSummaryTotal) }}</td>
                    </tr>
                </table>
            </b-col>

            <b-col lg="6">
                <table class="table table-bordered table-fit-more">
                    <tr>
                        <th>Caregiver Summary</th>
                        <th>Amount Due</th>
                    </tr>
                    <tr v-for="item in caregiverSummary">
                        <td>{{ item.name }}</td>
                        <td>{{ numberFormat(item.total) }}</td>
                    </tr>
                    <tr>
                        <th>Total</th>
                        <td>{{ numberFormat(caregiverSummaryTotal) }}</td>
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
                </b-form>
            </b-col>
            <b-col lg="4" class="text-right">
                <b-btn :href="`/business/statements/payments/${payment.id}/xls`" variant="success">Export to Excel</b-btn>
                <b-btn :href="`/business/statements/payments/${payment.id}/pdf`"><i class="fa fa-file-pdf-o"></i> PDF Statement</b-btn>
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

    export default {
        name: "ItemizedPayment",

        mixins: [FormatsNumbers, FormatsDates],

        props: {
            payment: {
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
                    return true;
                };

                return this.items.filter(filterFn).map(item => {
                    item.invoice_name = item.invoice.name;
                    item.client_name = item.client.nameLastFirst;
                    item.caregiver_name = item.caregiver ? item.caregiver.nameLastFirst : "";
                    return item;
                });
            },

            clientSummary() {
                this.clientSummaryTotal = 0;
                const summary = this.items.reduce((summary, item) => {
                    const clientId = item.client ? item.client.id : 0;
                    if (!summary[clientId]) {
                        summary[clientId] = {
                            name: item.client ? item.client.nameLastFirst : "Unknown",
                            total: 0
                        }
                    }
                    summary[clientId].total += parseFloat(item.amount_due || 0);
                    this.clientSummaryTotal += parseFloat(item.amount_due || 0);
                    return summary;
                }, {});
                return Object.values(summary).sort((a, b) => a.name < b.name ? -1 : 1);
            },

            caregiverSummary() {
                this.caregiverSummaryTotal = 0;
                const summary = this.items.reduce((summary, item) => {
                    const caregiverId = item.caregiver ? item.caregiver.id : 0;
                    if (!summary[caregiverId]) {
                        summary[caregiverId] = {
                            name: item.caregiver ? item.caregiver.nameLastFirst : "Unknown",
                            total: 0
                        }
                    }
                    summary[caregiverId].total += parseFloat(item.amount_due || 0);
                    this.caregiverSummaryTotal += parseFloat(item.amount_due || 0);
                    return summary;
                }, {});
                return Object.values(summary).sort((a, b) => a.name < b.name ? -1 : 1);
            },

            title() {
                let title = `Itemized View of Payment #${this.payment.id}. Payment Amount: $${this.numberFormat(this.payment.amount)}`;
                if (this.payment.amount < 0) {
                    title += " (Refund)";
                }
                return title;
            },
        },

        data() {
            return {
                caregivers: [],
                clients: [],
                clientId: null,
                caregiverId: null,
                clientSummaryTotal: 0,
                caregiverSummaryTotal: 0,
                showSummary: true,
                fields: [
                    {
                        key: "date",
                        formatter: val => val ? this.formatDateTime(val) : '-',
                        sortable: true,
                    },
                    {
                        key: "invoice_name",
                        label: "Invoice #",
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
                        key: "rate",
                        formatter: val => this.numberFormat(val),
                        sortable: true,
                    },
                    {
                        key: "total",
                        formatter: val => this.numberFormat(val),
                        sortable: true,
                    },
                    {
                        key: "amount_due",
                        label: "Amount Due by Payer",
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
        },

        mounted() {
            this.loadClients();
            this.loadCaregivers();
        }
    }
</script>

<style scoped>
    .table-fit-more td {
        font-size: 12px;
    }
</style>