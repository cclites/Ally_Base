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
                    <th width="100">Amount</th>
                    <td>{{ moneyFormat(transaction.amount) }}</td>
                </tr>
                <tr v-if="transaction.deposit && transaction.deposit.adjustment">
                    <th>Manual Adjustment</th>
                    <th>Yes</th>
                </tr>
                <tr v-if="transaction.deposit && transaction.deposit.notes">
                    <th>Notes</th>
                    <td>{{ transaction.deposit.notes }}</td>
                </tr>
                <tr v-if="transaction.payment && transaction.payment.notes">
                    <th>Notes</th>
                    <td>{{ transaction.payment.notes }}</td>
                </tr>
            </table>
        </b-card>

        <b-row>
            <b-col cols="12" class="with-padding-bottom">
                <b-button type="button" @click="showHideSummary()" variant="primary">{{ summaryButtonText }}</b-button>
            </b-col>
            <b-col lg="6" v-show="showSummary">
                <b-card header="Client Summary"
                        header-text-variant="white"
                        header-bg-variant="info">
                    <div class="pull-right hidden-print">
                        <button type="button" class="btn btn-default" @click="printClientSummary()" style="margin-top: -110px">Print</button>
                    </div>
                    <div id="client-charge-summary">
                        <h5 class="d-none d-print-block">Client Summary for Transaction #{{ transaction.id }}</h5>
                        <div class="table-responsive">
                            <b-table bordered striped hover show-empty
                                     :fields="clientSummaryFields"
                                     :items="clientSummary">
                            </b-table>
                        </div>
                    </div>
                </b-card>
            </b-col>
            <b-col lg="6" v-show="showSummary">
                <b-card header="Caregiver Summary"
                        header-text-variant="white"
                        header-bg-variant="info">
                    <div class="pull-right hidden-print">
                        <button type="button" class="btn btn-default" @click="printCaregiverSummary()" style="margin-top: -110px">Print</button>
                    </div>
                    <div class="table-responsive">
                        <div id="caregiver-payment-summary">
                            <h5 class="d-none d-print-block">Caregiver Summary for Transaction #{{ transaction.id }}</h5>
                            <b-table bordered striped hover show-empty
                                     :fields="caregiverSummaryFields"
                                     :items="caregiverSummary">
                            </b-table>
                        </div>
                    </div>
                </b-card>
            </b-col>
        </b-row>

        <loading-card v-show="loading"></loading-card>

        <b-row v-show="!loading">
            <b-col lg="12">
                <b-card
                        header="Related Shifts"
                        header-text-variant="white"
                        header-bg-variant="info"
                >
                    <div class="text-right">
                        <b-btn :href="urlPrefix + 'shifts' + queryString + '&export=1'" variant="success"><i class="fa fa-file-excel-o"></i> Export to Excel</b-btn>
                        <b-btn @click="printTable()" variant="primary"><i class="fa fa-print"></i> Print</b-btn>
                    </div>
                    <b-row class="my-3">
                        <b-col cols="6">
                            <b-form-select v-model="filterClientId">
                                <option value="">All Clients</option>
                                <option v-for="item in clients" :value="item.id" :key="item.id">{{ item.name }}</option>
                            </b-form-select>
                        </b-col>
                        <b-col cols="6">
                            <b-form-select v-model="filterCaregiverId">
                                <option value="">All Caregivers</option>
                                <option v-for="item in caregivers" :value="item.id" :key="item.id">{{ item.name }}</option>
                            </b-form-select>
                        </b-col>
                    </b-row>
                    
                    <div class="table-responsive">
                        <b-table bordered striped hover show-empty
                                 :fields="shiftFields"
                                 :items="filteredShifts"
                                 :sort-by.sync="sortBy"
                                 :sort-desc.sync="sortDesc"
                                 class="shift-table"
                        >
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
                    </div>
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
                        formatter: (value) => { return this.formatDateTimeFromUTC(value) }
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
                    {
                        key: 'actions',
                        class: 'hidden-print'
                    }
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
                        key: 'amount',
                        formatter: (value) => { return this.moneyFormat(value) },
                        sortable: true
                    }
                ],
                'shifts': [],
                'clientSummary': [],
                'caregiverSummary': [],
                'urlPrefix': '/business/reports/data/',
                'queryString': '?transaction_id=' + this.transaction.id,
                'showSummary': false,
                'loading': false,
                filterCaregiverId: '',
                filterClientId: '',
            }
        },

        computed: {
            summaryButtonText() {
                return (this.showSummary) ? 'Hide Summary' : 'Show Summary';
            },

            filteredShifts() {
                let results = [];

                this.shifts.forEach( (item) => {

                    if (this.filterClientId != '' && this.filterClientId != item.client_id) {
                        return;
                    }

                    if (this.filterCaregiverId != '' && this.filterCaregiverId != item.caregiver_id) {
                        return;
                    }

                    results.push(item);
                });

                return results;
            },

            clients() {
                let results = {};

                this.shifts.forEach( (item) => {
                    
                    if (!results[item.client_id]) {
                        results[item.client_id] = { id: item.client_id, name: item.client_name };
                    }

                });

                return results;
            },

            caregivers() {
                let results = {};

                this.shifts.forEach( (item) => {
                    
                    if (!results[item.caregiver_id]) {
                        results[item.caregiver_id] = { id: item.caregiver_id, name: item.caregiver_name };
                    }

                });

                return results;
            },

        },

        mounted() {
            this.loadData();
        },

        methods: {

            printTable() {
                $(".shift-table").print();
            },

            printClientSummary() {
                $('#client-charge-summary').print();
            },

            printCaregiverSummary() {
                $('#caregiver-payment-summary').print();
            },

            loadData() {
                this.loading = true;

                axios.get(this.urlPrefix + 'caregiver_payments' + this.queryString)
                    .then(response => {
                        if (Array.isArray(response.data)) {
                            this.caregiverSummary = response.data;
                        }
                        else {
                            this.caregiverSummary = [];
                        }
                    });

                axios.get(this.urlPrefix + 'client_charges' + this.queryString)
                    .then(response => {
                        if (Array.isArray(response.data)) {
                            this.clientSummary = response.data;
                        }
                        else {
                            this.clientSummary = [];
                        }
                    });

                axios.get(this.urlPrefix + 'shifts' + this.queryString + "&reconciliation_report=1")
                    .then(response => {
                        if (Array.isArray(response.data)) {
                            this.shifts = response.data;
                        }
                        else {
                            this.shifts = [];
                        }
                        this.loading = false;
                    })
                    .catch(error => {
                        this.loading = false;
                    });
            },

            showHideSummary() {
                this.showSummary = !this.showSummary;
            }
        },
    }
</script>
