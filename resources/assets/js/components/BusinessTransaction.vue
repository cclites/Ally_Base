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
                    <td>{{ numberFormat(transaction.amount) }}</td>
                </tr>
            </table>
        </b-card>

        <b-row>
            <b-col lg="12">
                <div class="text-right">
                    <b-btn :href="'/business/reports/data/shifts?transaction_id=' + transaction.id + '&export=1'" variant="success"><i class="fa fa-file-excel-o"></i> Export to Excel</b-btn>
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
                        <template slot="checked_in_time" scope="data">
                            {{ formatDateTimeFromUTC(data.value) }}
                        </template>
                        <template slot="client_name" scope="row">
                            <a :href="'/business/clients/' + row.item.client_id">{{ row.item.client_name }}</a>
                        </template>
                        <template slot="caregiver_name" scope="row">
                            <a :href="'/business/caregivers/' + row.item.caregiver_id">{{ row.item.caregiver_name }}</a>
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
                'shifts': [],
                'shiftFields': [
                    {
                        key: 'checked_in_time',
                        label: 'Date',
                        sortable: true,
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
                    },
                    {
                        key: 'provider_fee',
                        label: 'Reg Rate',
                        sortable: true,
                    },
                    {
                        key: 'ally_fee',
                        label: 'Ally Fee',
                        sortable: true,
                    },
                    {
                        key: 'hourly_total',
                        label: 'Total Hourly',
                        sortable: true,
                    },
                    {
                        key: 'caregiver_total',
                        label: 'CG Total',
                        sortable: true,
                    },
                    {
                        key: 'provider_total',
                        label: 'Reg Total',
                        sortable: true,
                    },
                    {
                        key: 'ally_total',
                        label: 'Ally Total',
                        sortable: true,
                    },
                    {
                        key: 'mileage_costs',
                        label: 'Mileage Costs',
                        sortable: true,
                    },
                    {
                        key: 'other_expenses',
                        label: 'Other',
                        sortable: true,
                    },
                    {
                        key: 'shift_total',
                        label: 'Shift Total',
                        sortable: true,
                    },
                    'actions'
                ]
            }
        },

        mounted() {
            this.loadData();
        },

        methods: {

            loadData() {
                axios.get('/business/reports/data/shifts?transaction_id=' + this.transaction.id)
                    .then(response => {
                        if (Array.isArray(response.data)) {
                            this.shifts = response.data;
                        }
                        else {
                            this.shifts = [];
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