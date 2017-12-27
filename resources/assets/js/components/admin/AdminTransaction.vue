<template>
    <div>
        <b-row>
            <b-col lg="6">
                <b-card
                        header="Transaction Details"
                        header-text-variant="white"
                        header-bg-variant="info"
                >
                    <table>
                        <tr>
                            <th>Ally Transaction ID</th>
                            <td>{{ transaction.id }}</td>
                        </tr>
                        <tr>
                            <th>Gateway Transaction ID</th>
                            <td>{{ transaction.transaction_id }}</td>
                        </tr>
                        <tr>
                            <th>Date</th>
                            <td>{{ formatDate(transaction.created_at) }}</td>
                        </tr>
                        <tr>
                            <th>Type</th>
                            <td>{{ (transaction.transaction_type === 'credit') ? 'Deposit' : 'Charge' }}</td>
                        </tr>
                        <tr>
                            <th>Amount</th>
                            <td>{{ numberFormat(transaction.amount) }}</td>
                        </tr>
                    </table>
                </b-card>
            </b-col>
            <b-col lg="6">
                <b-card
                        header="Payer/Payee Details"
                        header-text-variant="white"
                        header-bg-variant="info"
                >
                    <table>
                        <tr>
                            <th>Type</th>
                            <td>{{ userType }}</td>
                        </tr>
                        <tr>
                            <th>Name</th>
                            <td><a :href="userLink">{{ user.name }}</a></td>
                        </tr>
                        <tr v-if="transaction.payment">
                            <th>Payment Type &nbsp;</th>
                            <td>{{ transaction.payment.payment_type || 'N/A' }}</td>
                        </tr>
                        <tr v-if="transaction.payment">
                            <th>Registry</th>
                            <td v-if="transaction.payment.business">{{ transaction.payment.business.name }}</td>
                            <td v-else>N/A</td>
                        </tr>
                    </table>
                </b-card>
            </b-col>
        </b-row>


        <b-row>
            <b-col lg="12">
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
                            {{ formatDate(data.value) }} {{ formatTime(data.value) }}
                        </template>
                        <template slot="client_name" scope="row">
                            <a href="#">{{ row.item.client_name }}</a>
                        </template>
                        <template slot="caregiver_name" scope="row">
                            <a href="#">{{ row.item.caregiver_name }}</a>
                        </template>
                        <template slot="actions" scope="row">

                        </template>
                    </b-table>
                </b-card>
            </b-col>
        </b-row>

    </div>
</template>

<script>
    import FormatsDates from '../../mixins/FormatsDates'
    import FormatsNumbers from '../../mixins/FormatsNumbers'

    export default {
        mixins: [
            FormatsDates,
            FormatsNumbers
        ],

        props: {
            'transaction': Object,
            'user': Object,
            'userType': String,
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
                        key: 'duration',
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
                    {
                        key: 'actions',
                        class: 'hidden-print'
                    }
                ]
            }
        },

        computed: {
            userLink() {
                switch(this.userType) {
                    default:
                        return '#';
                }
            },
        },

        mounted() {
            this.loadData();
        },

        methods: {            
            loadData() {
                axios.get('/admin/shifts/data?transaction_id=' + this.transaction.id)
                    .then(response => {
                        if (Array.isArray(response.data)) {
                            this.shifts = response.data.map(function(item) {
                                item.checked_in_time = moment.utc(item.checked_in_time).local();
                                item.client_name = item.client.nameLastFirst;
                                item.caregiver_name = item.caregiver.nameLastFirst;
                                return item;
                            })
                        }
                        else {
                            this.shifts = [];
                        }
                    });
            }
        },
    }
</script>
