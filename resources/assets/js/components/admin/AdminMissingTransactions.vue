<template>
    <b-card>
        <b-row class="mb-2">
            <b-col lg="6">

            </b-col>
            <b-col lg="6" class="text-right">
                <b-form-input v-model="filter" placeholder="Type to Search" />
            </b-col>
        </b-row>

        <loading-card v-show="loading"></loading-card>

        <div v-if="! loading" class="table-responsive">
            <b-table bordered striped hover show-empty
                     :items="items"
                     :fields="fields"
                     :filter="filter"
                     :sort-by.sync="sortBy"
                     :sort-desc.sync="sortDesc"
            >
            </b-table>
        </div>
    </b-card>
</template>

<script>
    import FormatsNumbers from "../../mixins/FormatsNumbers";

    export default {
        mixins: [FormatsNumbers],

        props: {},

        data() {
            return {
                sortBy: null,
                sortDesc: false,
                filter: null,
                loading: false,
                items: [],
                fields: [
                    {
                        key: 'id',
                        label: 'Internal ID',
                        sortable: true,
                    },
                    {
                        key: 'transaction_id',
                        label: 'Gateway ID',
                        sortable: true,
                    },
                    {
                        key: 'amount',
                        sortable: true,
                        formatter: this.numberFormat
                    },
                    {
                        key: 'transaction_type',
                        sortable: true,
                    },
                    {
                        key: 'last_action',
                        sortable: true,
                    },
                    {
                        key: 'last_status',
                        sortable: true,
                    },
                    {
                        key: 'owner_name',
                        sortable: true,
                    },
                    {
                        key: 'created_at',
                        sortable: true,
                    },
                ]
            }
        },

        mounted() {
            this.loadData();
        },

        methods: {

            loadData() {
                this.loading = true;
                axios.get('/admin/missing_transactions?json=1')
                    .then(response => {
                        this.items = response.data.map(transaction => {
                            transaction.last_action = (transaction.last_history) ? transaction.last_history.action : null;
                            transaction.last_status = (transaction.last_history) ? transaction.last_history.status : null;
                            transaction.owner_name = (transaction.owner) ? transaction.owner.name : null;
                            return transaction;
                        })
                            .filter(transaction => {
                                return transaction.last_status !== 'failed';
                            });
                        this.loading = false;
                    })
                    .catch(e => {
                        this.loading = false;
                    });
            },
        },
    }
</script>
