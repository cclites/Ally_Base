<template>
    <b-card>
        <b-row class="mb-2">
            <b-col lg="6">
            </b-col>
            <b-col lg="6" class="text-right">
                <b-form-input v-model="filter" placeholder="Type to Search" />
            </b-col>
        </b-row>

        <div class="table-responsive">
            <b-table bordered striped hover show-empty
                     :items="items"
                     :fields="fields"
                     :filter="filter"
                     :sort-by.sync="sortBy"
                     :sort-desc.sync="sortDesc"
            >
                <template slot="actions" scope="row">
                    <b-btn size="sm" @click="markFailed(row.item)" variant="success" v-if="row.item.success">Mark Failed</b-btn>
                    <b-btn size="sm" @click="markSuccessful(row.item)" variant="danger" v-else>Mark Successful</b-btn>
                    <b-btn size="sm" :href="'/admin/transactions/' + row.item.transaction_id" v-if="row.item.transaction_id">View Transaction</b-btn>
                </template>
            </b-table>
        </div>
    </b-card>
</template>

<script>
    export default {

        props: {},

        data() {
            return {
                sortBy: null,
                sortDesc: false,
                filter: null,
                items: [],
                business_id: "",
                businesses: [],
                fields: [
                    {
                        key: 'name',
                        label: 'Name',
                        sortable: true,
                    },
                    {
                        key: 'deposit_type',
                        label: 'Deposit Type',
                        sortable: true,
                    },
                    {
                        key: 'amount',
                        label: 'Amount',
                        sortable: true,
                    },
                    {
                        key: 'created_at',
                        label: 'Date Deposited',
                        sortable: true,
                    },
                    {
                        key: 'last_action',
                        label: 'Last Action',
                        sortable: true,
                    },
                    {
                        key: 'gateway_id',
                        label: 'Transaction ID',
                        sortable: true,
                    },
                    'actions',
                ]
            }
        },

        mounted() {
            // this.loadBusinesses();
            this.loadItems();
        },

        methods: {
            loadItems() {
                axios.get('/admin/deposits/failed?json=1')
                    .then(response => {
                        this.items = response.data.map(function(item) {
                            item.name = (item.deposit_type == 'business') ? item.business.name : item.caregiver.nameLastFirst;
                            item.gateway_id = (item.transaction) ? item.transaction.transaction_id : '';
                            item.last_action = (item.transaction && item.transaction.last_history) ? item.transaction.last_history.created_at : '';
                            return item;
                        });
                    });
            },
            markSuccessful(deposit) {
                let form = new Form();
                form.post('/admin/deposits/successful/' + deposit.id)
                    .then(response => {
                        deposit.success = true;
                    });
            },
            markFailed(deposit) {
                let form = new Form();
                form.post('/admin/deposits/failed/' + deposit.id)
                    .then(response => {
                        deposit.success = false;
                    });
            }
        }
    }
</script>

