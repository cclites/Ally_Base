<template>
    <b-card>
        <b-row class="mb-2">
            <b-col lg="6">

                <b-form-input v-model="filter" placeholder="Type to Search" />
            </b-col>
            <b-col lg="6" class="text-right">

                <b-button variant="info" @click=" loadItems() ">Generate Report</b-button>
            </b-col>
        </b-row>

        <loading-card v-show="loading"></loading-card>

        <div v-show="! loading" class="table-responsive">
            <b-table bordered striped hover show-empty
                     :items="items"
                     :fields="fields"
                     :filter="filter"
                     :sort-by.sync="sortBy"
                     :sort-desc.sync="sortDesc"
            >
                <template slot="actions" scope="row">
                    <b-btn size="sm" @click="markFailed(row.item)" variant="danger">True Failure</b-btn>
                    <b-btn size="sm" @click="markSuccessful(row.item)" variant="success">Mark Successful</b-btn>
                    <b-btn size="sm" :href="'/admin/transactions/' + row.item.id">View Transaction</b-btn>
                </template>
            </b-table>
        </div>
    </b-card>
</template>

<script>
    import FormatsNumbers from '../../mixins/FormatsNumbers';

    export default {

        mixins: [FormatsNumbers],

        data() {
            return {
                sortBy: null,
                sortDesc: false,
                filter: null,
                items: [],
                loading: false,
                business_id: "",
                businesses: [],
                fields: [
                    {
                        key: 'id',
                        label: 'ID',
                        sortable: true,
                    },
                    {
                        key: 'name',
                        sortable: true,
                    },
                    {
                        key: 'business_name',
                        label: 'Chain',
                        sortable: true,
                    },
                    {
                        key: 'ally_type',
                        label: 'Ally Type',
                        sortable: true,
                    },
                    {
                        key: 'transaction_id',
                        label: 'Gateway ID',
                        sortable: true,
                    },
                    {
                        key: 'transaction_type',
                        label: 'Gateway Type',
                        sortable: true,
                    },
                    {
                        key: 'method_type',
                        label: 'Method Type',
                        formatter: (value) => { return _.replace(value, "App\\", '') }
                    },
                    {
                        key: 'amount',
                        sortable: true,
                        formatter: (value) => { return this.moneyFormat(value) }
                    },
                    {
                        key: 'created_at',
                        label: 'Creation Date',
                        sortable: true,
                    },
                    {
                        key: 'last_history_action',
                        label: 'Last Action',
                        sortable: true,
                    },
                    {
                        key: 'last_history_date',
                        label: 'Last Action Date',
                        sortable: true,
                    },
                    'actions',
                ]
            }
        },

        mounted() {
            // this.loadBusinesses();
        },

        methods: {
            loadItems() {
                this.loading = true;
                axios.get('/admin/failed_transactions/?json=1')
                    .then(response => {
                        this.items = response.data.map(function(item) {
                            if (item.deposit) {
                                item.ally_type = 'deposit';
                                item.related = item.deposit;
                            }
                            else if (item.payment) {
                                item.ally_type = 'payment';
                                item.related = item.payment;
                            }
                            else {
                                item.related = {};
                            }

                            item.name = '';
                            item.business_name = '';
                            if (item.related.client) {
                                item.name = item.related.client.name;
                                if (item.related.client.business && item.related.client.business.chain) {
                                    item.business_name = item.related.client.business.chain.name;
                                }
                            }
                            else if (item.related.caregiver) {
                                item.name = item.related.caregiver.name;
                                if (item.related.caregiver.business_chains) {
                                    item.business_name = item.related.caregiver.business_chains[0].name;
                                }
                            }
                            else if (item.related.business) {
                                item.name = item.related.business.name;
                                item.business_name = item.related.business.name;
                                if (item.related.business.chain) {
                                    item.business_name = item.related.business.chain.name;
                                }
                            }

                            item.last_history_date = (item.last_history) ? item.last_history.created_at : '';
                            item.last_history_action = (item.last_history) ? item.last_history.action : '';
                            return item;
                        });
                        this.loading = false;
                    })
                    .catch(e => {
                        this.loading = false;
                    });
            },
            markSuccessful(transaction) {
                return this.markFailed(transaction, 0);
            },
            markFailed(transaction, failed = 1) {
                let failedTxt = (failed) ? 'failed' : 'successful';
                if (!confirm('Are you sure you want to mark ' + transaction.name + '\'s transaction as ' + failedTxt + '?')) {
                    return;
                }
                let form = new Form({ failed });
                form.patch('/admin/failed_transactions/' + transaction.id)
                    .then(response => {
                        this.items = this.items.filter(item => item.id !== transaction.id);
                    });
            }
        }
    }
</script>

