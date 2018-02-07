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
                    <b-btn :href="'/admin/transactions/' + row.item.last_transaction_id" v-if="row.item.last_transaction_id">View Last Transaction</b-btn>
                </template>
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
                items: [],
                fields: [
                    {
                        key: 'name',
                        sortable: true,
                    },
                    {
                        key: 'type',
                        sortable: true,
                    },
                    {
                        key: 'id',
                        sortable: true,
                    },
                    {
                        key: 'business',
                        sortable: true,
                    },
                    {
                        key: 'payment_outstanding',
                        sortable: true,
                        formatter: this.numberFormat
                    },
                    {
                        key: 'deposit_outstanding',
                        sortable: true,
                        formatter: this.numberFormat
                    },
                    {
                        key: 'created_at',
                        label: 'Hold Date',
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
                axios.get('/admin/reports/on_hold?json=1')
                    .then(response => {
                        this.items = response.data;
                    });
            },
        },
    }
</script>
