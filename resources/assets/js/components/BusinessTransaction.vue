<template>
    <div>
        <b-card
            header="Transaction Details"
            header-text-variant="white"
            header-bg-variant="info"
            >
            <table>
                <tr>
                    <th>Date</th>
                    <td>{{ formatDate(transaction.created_at) }}</td>
                </tr>
                <tr>
                    <th>Type</th>
                    <td>{{ (transaction.transaction_type === 'credit') ? 'Deposit' : 'Withdrawal/Charge' }}</td>
                </tr>
                <tr>
                    <th>Amount</th>
                    <td>{{ moneyFormat(transaction.amount) }}</td>
                </tr>
            </table>
        </b-card>

        <b-row>
            <b-col lg="6">
                <b-card header="Client Summary"
                        header-text-variant="white"
                        header-bg-variant="info">
                    <b-table bordered striped hover show-empty
                             :fields="clientSummaryFields"
                             :items="clientSummary">
                    </b-table>
                </b-card>
            </b-col>
            <b-col lg="6">
                <b-card header="Caregiver Summary"
                        header-text-variant="white"
                        header-bg-variant="info">
                    <b-table bordered striped hover show-empty
                             :fields="caregiverSummaryFields"
                             :items="caregiverSummary">
                    </b-table>
                </b-card>
            </b-col>
        </b-row>

        <b-row>
            <b-col lg="12">
                <div class="text-right">
                    <b-btn :href="urlPrefix + 'shifts' + queryString + '&export=1'" variant="success"><i class="fa fa-file-excel-o"></i> Export to Excel</b-btn>
                    <b-btn href="javascript:print()" variant="primary"><i class="fa fa-print"></i> Print</b-btn>
                </div>
                <b-card
                        header="Related Shifts"
                        header-text-variant="white"
                        header-bg-variant="info"
                >
                    <b-table bordered striped hover show-empty
                             :fields="shiftFields"
                             :items="shifts"
                             :sort-by.sync="sortBy"
                             :sort-desc.sync="sortDesc"
                             class="shift-table"
                    >
                        <template slot="client_name" scope="row">
                            <a :href="'/business/clients/' + row.item.client_id">{{ row.item.client.name }}</a>
                        </template>
                        <template slot="caregiver_name" scope="row">
                            <a :href="'/business/caregivers/' + row.item.caregiver_id">{{ row.item.caregiver.name }}</a>
                        </template>
                        <template slot="actions" scope="row">
                            <b-btn size="sm" :href="'/business/shifts/' + row.item.id" variant="info" v-b-tooltip.hover title="View"><i class="fa fa-eye"></i></b-btn>
                        </template>
                    </b-table>
                </b-card>
            </b-col>
        </b-row>
    </div>
</template>

<script>
    import FormatsDates from '../mixins/FormatsDates'
    import FormatsNumbers from '../mixins/FormatsNumbers'

    export default {
        mixins: [
            FormatsDates,
            FormatsNumbers
        ],

        props: {
            'transaction': Object,
        },

        data() {
            return {
                'sortBy': 'checked_in_time',
                'sortDesc': false,
                'shiftFields': [
                    {
                        key: 'checked_in_time',
                        label: 'Date',
                        sortable: true,
                        formatter: this.formatDateTimeFromUTC,
                    },
                    {
                        key: 'hours',
                        label: 'Hours',
                        sortable: true,
                    },
                    {
                        key: 'client_name',
                        label: 'Client',
                        sortable: true,
                    },
                    {
                        key: 'caregiver_name',
                        label: 'Caregiver',
                        sortable: true,
                    },
                    {
                        key: 'caregiver_rate',
                        label: 'CG Rate',
                        sortable: true,
                        formatter: (value) => { return this.moneyFormat(value) }
                    },
                    {
                        key: 'provider_fee',
                        label: 'Reg Rate',
                        sortable: true,
                        formatter: (value) => { return this.moneyFormat(value) }
                    },
                    {
                        key: 'ally_fee',
                        label: 'Ally Fee',
                        sortable: true,
                        formatter: (value) => { return this.moneyFormat(value) }
                    },
                    {
                        key: 'hourly_total',
                        label: 'Total Hourly',
                        sortable: true,
                        formatter: (value) => { return this.moneyFormat(value) }
                    },
                    {
                        key: 'caregiver_total',
                        label: 'CG Total',
                        sortable: true,
                        formatter: (value) => { return this.moneyFormat(value) }
                    },
                    {
                        key: 'provider_total',
                        label: 'Reg Total',
                        sortable: true,
                        formatter: (value) => { return this.moneyFormat(value) }
                    },
                    {
                        key: 'ally_total',
                        label: 'Ally Total',
                        sortable: true,
                        formatter: (value) => { return this.moneyFormat(value) }
                    },
                    {
                        key: 'mileage_costs',
                        label: 'Mileage Costs',
                        sortable: true,
                        formatter: (value) => { return this.moneyFormat(value) }
                    },
                    {
                        key: 'other_expenses',
                        label: 'Other',
                        sortable: true,
                        formatter: (value) => { return this.moneyFormat(value) }
                    },
                    {
                        key: 'shift_total',
                        label: 'Shift Total',
                        sortable: true,
                        formatter: (value) => { return this.moneyFormat(value) }
                    },
                    'actions'
                ],
                'clientSummaryFields': [
                    {
                        key: 'name',
                        sortable: true
                    },
                    {
                        key: 'cg_total',
                        formatter: (value) => { return this.moneyFormat(value) },
                        sortable: true
                    },
                    {
                        key: 'hours',
                        sortable: true
                    },
                    {
                        key: 'ally_total',
                        formatter: (value) => { return this.moneyFormat(value) },
                        sortable: true
                    },
                    {
                        key: 'provider_total',
                        formatter: (value) => { return this.moneyFormat(value) },
                        sortable: true
                    },
                    {
                        key: 'total',
                        formatter: (value) => { return this.moneyFormat(value) },
                        sortable: true
                    }
                ],
                'caregiverSummaryFields': [
                    {
                        key: 'name',
                        sortable: true
                    },
                    {
                        key: 'hours',
                        sortable: true
                    },
                    {
                        key: 'total',
                        formatter: (value) => { return this.moneyFormat(value) },
                        sortable: true
                    }
                ],
                'shifts': [],
                'clientSummary': [],
                'caregiverSummary': [],
                'urlPrefix': '/business/reports/data/',
                'queryString': '?transaction_id=' + this.transaction.id,
            }
        },

        mounted() {
            this.loadData();
        },

        methods: {

            loadData() {
                axios.get(this.urlPrefix + 'caregiver_payments' + this.queryString)
                    .then(response => {
                        if (Array.isArray(response.data)) {
                            this.items.caregiverPayments = response.data;
                        }
                        else {
                            this.items.caregiverPayments = [];
                        }
                    });
                axios.get(this.urlPrefix + 'client_charges' + this.queryString)
                    .then(response => {
                        if (Array.isArray(response.data)) {
                            this.items.clientCharges = response.data;
                        }
                        else {
                            this.items.clientCharges = [];
                        }
                    });
                axios.get(this.urlPrefix + 'shifts' + this.queryString)
                    .then(response => {
                        if (Array.isArray(response.data)) {
                            this.items.shifts = response.data;
                        }
                        else {
                            this.items.shifts = [];
                        }
                    });
            }
        },
    }
</script>

<style>
    @media print {
        body * {
            visibility: hidden;
        }
        .shift-table, .shift-table * {
            visibility: visible;
        }
        .shift-table {
            position: absolute;
            left: 0;
            top: 0;
        }
    }
</style>