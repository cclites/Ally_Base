<template>
    <b-card>
        <b-row class="mb-2">
            <b-col lg="6">
                <b-form-select
                        v-model="businessId"
                        required
                >
                    <option value="">--Filter by Provider--</option>
                    <option v-for="business in businesses" :value="business.id" :key="business.id">{{ business.name }}</option>
                </b-form-select>
            </b-col>
            <b-col lg="6" class="text-right">
                <b-form-input v-model="filter" placeholder="Type to Search" />
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
                    <b-btn size="sm" @click="removeHold(row.item)" variant="primary">Remove Hold</b-btn>
                    <b-btn size="sm" :href="'/admin/transactions/' + row.item.last_transaction_id" v-if="row.item.last_transaction_id">View Last Transaction</b-btn>
                </template>
            </b-table>
        </div>
    </b-card>
</template>

<script>
    import FormatsNumbers from "../../mixins/FormatsNumbers";
    import FormatsDates from "../../mixins/FormatsDates";

    export default {
        mixins: [FormatsNumbers, FormatsDates],

        props: {},

        data() {
            return {
                sortBy: null,
                sortDesc: false,
                filter: null,
                items: [],
                loading: false,
                fields: [
                    {
                        key: 'name',
                        sortable: true,
                    },
                    {
                        key: 'type',
                        sortable: true,
                        formatter: x => _.startCase(x),
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
                    // {
                    //     key: 'payment_outstanding',
                    //     label: 'Charges Outstanding',
                    //     sortable: true,
                    //     formatter: this.numberFormat
                    // },
                    // {
                    //     key: 'deposit_outstanding',
                    //     label: 'Deposits Outstanding',
                    //     sortable: true,
                    //     formatter: this.numberFormat
                    // },
                    {
                        key: 'unpaid_invoices',
                        sortable: true,
                        // formatter: this.numberFormat
                    },
                    {
                        key: 'created_at',
                        label: 'Hold Date',
                        sortable: true,
                        formatter: x => this.formatDateTimeFromUTC(x),
                    },
                    'actions'
                ],
                businesses: [],
                businessId: "",
            }
        },

        mounted() {
            this.loadBusinesses();
            this.loadData();
        },

        methods: {

            loadData() {
                this.loading = true;
                axios.get('/admin/reports/on_hold?json=1&business_id=' + this.businessId)
                    .then(response => {
                        this.items = response.data;
                        this.loading = false;
                    })
                    .catch(e => {
                        this.loading = false;
                    });
            },

            loadBusinesses() {
                axios.get('/admin/businesses').then(response => this.businesses = response.data);
            },

            removeHold(item) {
                let form = new Form();
                let url = '/admin/users/' + item.id + '/hold';
                if (item.type === 'business') {
                    url = '/admin/businesses/' + item.id + '/hold'
                }
                form.submit('delete', url)
                    .then(response => {
                        this.items = this.items.filter(current => current.id !== item.id);
                    });
            },
        },

        watch: {
            businessId() {
                this.loadData();
            }
        }
    }
</script>
