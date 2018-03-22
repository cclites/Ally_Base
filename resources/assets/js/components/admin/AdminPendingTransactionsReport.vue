<template>
    <b-card>
        <b-row class="mb-2">
            <b-col lg="6">
                <b-form inline>
                    <b-form-select
                            v-model="businessId"
                            required
                    >
                        <option value="">--Filter by Provider--</option>
                        <option v-for="business in businesses" :value="business.id">{{ business.name }}</option>
                    </b-form-select>
                    <b-form-select
                            v-model="hideZeros"
                            required
                    >
                        <option :value="true">Hide Zero Dollar Outstanding</option>
                        <option :value="false">Show Zero Dollar Outstanding</option>
                    </b-form-select>
                </b-form>
            </b-col>
            <b-col lg="6" class="text-right">
                <b-form-input v-model="filter" placeholder="Type to Search" />
            </b-col>
        </b-row>

        <loading-card v-show="loading"></loading-card>
        <div class="table-responsive" v-show="!loading">
            <b-table bordered striped hover show-empty
                     :items="itemsFiltered"
                     :fields="fields"
                     :filter="filter"
                     :sort-by.sync="sortBy"
                     :sort-desc.sync="sortDesc"
            >
                <!--<template slot="actions" scope="row">-->
                    <!--<b-btn size="sm" :href="'/admin/transactions/' + row.item.last_transaction_id" v-if="row.item.last_transaction_id">View Last Transaction</b-btn>-->
                    <!--<span v-else>No Last Transaction</span>-->
                <!--</template>-->
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
                        label: 'Registry',
                        sortable: true,
                    },
                    {
                        key: 'payment_outstanding',
                        label: 'Charges Outstanding',
                        sortable: true,
                        formatter: this.numberFormat
                    },
                    {
                        key: 'deposit_outstanding',
                        label: 'Deposits Outstanding',
                        sortable: true,
                        formatter: this.numberFormat
                    },
                    {
                        key: 'flags',
                        sortable: true,
                    },
                    // 'actions'
                ],
                businesses: [],
                businessId: "",
                hideZeros: true,
                loading: false,
            }
        },

        mounted() {
            this.loadBusinesses();
            this.loadData();
        },

        computed: {
            itemsFiltered() {
                let items = this.items.slice();
                if (this.hideZeros) {
                    items = items.filter(item => {
                        return item.payment_outstanding > 0 || item.deposit_outstanding > 0;
                    })
                }
                if (this.businessId) {
                    items = items.filter(item => {
                        return item.business_id == this.businessId;
                    });
                }
                return items;
            }
        },

        methods: {

            loadData() {
                this.loading = true;
                axios.get('/admin/reports/pending_transactions?json=1')
                    .then(response => {
                        this.items = response.data;
                        this.loading = false;
                    });
            },

            loadBusinesses() {
                axios.get('/admin/businesses').then(response => this.businesses = response.data);
            },
        },

        watch: {
        }
    }
</script>
