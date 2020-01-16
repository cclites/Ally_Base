<template>
    <b-card>
        <b-row class="mb-2">
            <b-col lg="3">
                <b-form-group label="Filter by Provider">
                    <b-form-select
                            v-model="businessId"
                            class="mr-2"
                            required
                    >
                        <option value="">All</option>
                        <option v-for="business in businesses" :value="business.id" :key="business.id">{{ business.name }}</option>
                    </b-form-select>
                </b-form-group>
            </b-col>
            <b-col lg="3">
                <b-form-group label="Zero Dollar Outstanding">
                    <b-form-select
                            v-model="hideZeros"
                            required
                    >
                        <option :value="true">Hide</option>
                        <option :value="false">Show</option>
                    </b-form-select>
                </b-form-group>
            </b-col>
            <b-col lg="3">
                <b-form-group label="Search">
                    <b-form-input v-model="filter" placeholder="Type to Search"/>
                </b-form-group>
            </b-col>
            <b-col lg="3">
                <b-form-group label="Filter by Type">
                    <b-form-select v-model="filters.type">
                        <option value="">All</option>
                        <option v-for="type in types" :value="type" :key="type">{{ upperFirst(type) }}</option>
                    </b-form-select>
                </b-form-group>
            </b-col>
            <b-col lg="3">
                <b-form-group label="Has Flags?">
                    <b-form-radio-group v-model="filters.flags" name="radioSubComponent">
                        <b-form-radio value="">All</b-form-radio>
                        <b-form-radio value="yes">Yes</b-form-radio>
                        <b-form-radio value="no">No</b-form-radio>
                    </b-form-radio-group>
                </b-form-group>
            </b-col>
            <b-col class="text-right">

                <b-button variant="info" @click=" loadItems() ">Generate Report</b-button>
            </b-col>
        </b-row>

        <b-row class="mb-2 d-flex flex-row justify-content-around">
            <div class="h5">Charges Outstanding <span :class="{ 'text-danger': outstandingCharges > 0 }">{{ moneyFormat(outstandingCharges) }}</span></div>
            <div class="h5">Deposits <span :class="{ 'text-success': deposits > 0 }">{{ moneyFormat(deposits) }}</span></div>
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
                filters: {
                    type: '',
                    flags: ''
                },
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
                        formatter: (value) => { return this.moneyFormat(value) }
                    },
                    {
                        key: 'deposit_outstanding',
                        label: 'Deposits Outstanding',
                        sortable: true,
                        formatter: (value) => { return this.moneyFormat(value) }
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
                types: ['client', 'caregiver', 'business']
            }
        },

        mounted() {
            this.loadBusinesses();
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
                if (this.filters.type !== '') {
                    items = items.filter(item => {
                        return item.type === this.filters.type;
                    });
                }
                if (this.filters.flags !== '') {
                    items = items.filter(item => {
                        switch (this.filters.flags) {
                            case 'yes':
                                return item.flags !== '';
                            case 'no':
                                return item.flags === '';
                        }
                    });
                }
                return items;
            },

            outstandingCharges() {
                return _.sumBy(this.itemsFiltered, 'payment_outstanding');
            },

            deposits() {
                return _.sumBy(this.itemsFiltered, 'deposit_outstanding');
            }
        },

        methods: {

            upperFirst(text) {
                return _.upperFirst(text);
            },
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

        watch: {}
    }
</script>
