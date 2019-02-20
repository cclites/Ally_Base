<template>
    <b-card
        header="Reconciliation Report"
        header-text-variant="white"
        header-bg-variant="info"
        >
        <div class="row">
            <div class="col-lg-3">
                <date-picker
                        v-model="start_date"
                        placeholder="Start Date"
                >
                </date-picker>
            </div>
            <div class="col-lg-3">
                <date-picker
                        v-model="end_date"
                        placeholder="End Date"
                >
                </date-picker>
            </div>
            <div class="col-lg-3">
                <b-form-group>
                    <b-form-checkbox-group v-model="types" name="types" :options="options">
                    </b-form-checkbox-group>
                </b-form-group>
            </div>
            <div class="col-lg-3 text-right">
                <b-btn @click="filterList()" variant="primary">Generate</b-btn>
            </div>
        </div>

        <div class="text-right mb-2">
            <b-btn href="/business/reports/reconciliation?export=1" variant="success"><i class="fa fa-file-excel-o"></i> Export to Excel</b-btn>
            <b-btn @click="printTable()" variant="primary"><i class="fa fa-print"></i> Print</b-btn>
        </div>

        <loading-card v-show="loading"></loading-card>

        <div v-show="! loading" class="table-responsive">
            <b-table bordered striped hover show-empty
                     :fields="fields"
                     :items="items"
                     :sort-by.sync="sortBy"
                     :sort-desc.sync="sortDesc"
                     class="shift-table"
            >
                <template slot="created_at" scope="data">
                    {{ formatDate(data.value) }}
                </template>
                <template slot="amount_deposited" scope="data">
                    {{ numberFormat(data.value) }}
                </template>
                <template slot="amount_withdrawn" scope="data">
                    {{ numberFormat(data.value) }}
                </template>
                <template slot="success" scope="data">
                    <span v-if="data.value" style="color: green">OK</span>
                    <span v-else style="color: darkred">Failed</span>
                </template>
                <template slot="actions" scope="row">
                    <b-btn size="sm" :href="statementUrl(row.item)">View Statement</b-btn>
                </template>
            </b-table>
        </div>
    </b-card>
</template>

<script>
    import FormatsDates from '../mixins/FormatsDates'
    import FormatsNumbers from '../mixins/FormatsNumbers'

    export default {
        mixins: [
            FormatsDates,
            FormatsNumbers
        ],

        props: {},

        data() {
            return {
                'sortBy': 'created_at',
                'sortDesc': true,
                'items': [],
                types: [],
                loading: false,
                start_date: moment().subtract(4, 'weeks').format('MM/DD/YYYY'),
                end_date: moment().format('MM/DD/YYYY'),
                options: [
                    {text: 'Deposits', value: 'deposits'},
                    {text: 'Withdrawls', value: 'withdrawls'}
                ],
                types: [],
                'fields': [
                    {
                        key: 'created_at',
                        label: 'Date',
                        sortable: true,
                    },
                    {
                        key: 'amount_deposited',
                        label: 'Deposited',
                        sortable: true,
                    },
                    {
                        key: 'amount_withdrawn',
                        label: 'Withdrawn',
                        sortable: true,
                    },
                    {
                        key: 'success',
                        label: 'Status'
                    },
                    {
                        key: 'actions',
                        class: 'hidden-print'
                    }
                ]
            }
        },

        mounted() {
            this.loadData();
        },
        methods: {
            filterList() {
                this.loadData();
            },

            printTable() {
                $(".shift-table").print();
            },

            loadData() {
                this.loading = true;
                axios.get('/business/reports/reconciliation', {
                        params: {
                            json: 1,
                            start_date: this.start_date,
                            end_date: this.end_date,
                            types: this.types,
                        }
                    })
                    .then(response => {
                        if (Array.isArray(response.data)) {
                            this.items = response.data;
                        } else {
                            this.items = [];
                        }
                        this.loading = false;
                    })
                    .catch(e => {
                        this.loading = false;
                    });
            },

            statementUrl(item)
            {
                if (item.payment_id) {
                    return `/business/statements/payments/${item.payment_id}/pdf`
                }
                return `/business/statements/deposits/${item.deposit_id}/pdf`
            }
        },
    }
</script>
