<template>
    <b-card>
        <b-row>
            <b-col lg="12">
                <b-card header="Select Date Range"
                        header-text-variant="white"
                        header-bg-variant="info"
                >
                    <b-form inline @submit.prevent="loadItems()">
                        <date-picker
                                v-model="start_date"
                                placeholder="Start Date"
                        >
                        </date-picker> &nbsp;to&nbsp;
                        <date-picker
                                v-model="end_date"
                                placeholder="End Date"
                        >
                        </date-picker>
                        <b-form-select
                            id="business_id"
                            name="business_id"
                            v-model="business_id"
                            required
                            >
                            <option value="">--Select a Provider--</option>
                            <option v-for="business in businesses" :value="business.id" :key="business.id">{{ business.name }}</option>
                        </b-form-select>
                        &nbsp;&nbsp;<b-button type="submit" variant="info">List Pending Deposits</b-button>
                        &nbsp;&nbsp;<b-button @click="processDeposits()" variant="danger" :disabled="processing">Process Deposits</b-button>
                    </b-form>
                </b-card>
            </b-col>
        </b-row>

        <loading-card v-if="isLoading"></loading-card>

        <div v-if="! isLoading">
            <b-row>
                <b-col sm="12">
                    <b>There are {{ totalItems }} transactions listed for a total amount of {{ numberFormat(totalAmount) }}.</b>
                </b-col>
            </b-row>
            <div class="table-responsive">
                <b-table bordered striped hover show-empty
                        :items="items"
                        :fields="fields"
                        :sort-by.sync="sortBy"
                        :sort-desc.sync="sortDesc"
                >
                </b-table>
            </div>
        </div>
    </b-card>
</template>

<script>
    import FormatsNumbers from '../../mixins/FormatsNumbers';

    export default {
        mixins: [FormatsNumbers],

        props: {},

        data() {
            return {
                sortBy: null,
                sortDesc: false,
                start_date: "08/01/2017", //moment().startOf('isoweek').subtract(7, 'days').format('MM/DD/YYYY'),
                end_date: moment().startOf('isoweek').subtract(1, 'days').format('MM/DD/YYYY'),
                business_id: "",
                businesses: [],
                deposits: [],
                missingAccounts: [],
                processing: false,
                loading: 2,
                fields: [
                    {
                        key: 'deposit_type',
                        label: 'Deposit Type',
                        sortable: true,
                    },
                    {
                        key: 'name',
                        label: 'Name',
                        sortable: true,
                    },
                    {
                        key: 'amount',
                        label: 'Amount',
                        sortable: true,
                    },
                    {
                        key: 'flag',
                        label: 'Flag',
                        sortable: true,
                    },
                ]
            }
        },

        mounted() {
            this.loadBusinesses();
        },

        computed: {
            items() {
                return this.deposits.map(item => {
                     item.flag = '';
                     if (this.missingAccounts.find(caregiver => { return item.caregiver_id == caregiver.id })) {
                         item.flag = 'Missing Bank Account';
                     }
                     return item;
                });
            },
            totalItems() {
                return this.deposits.length;
            },
            totalAmount() {
                return this.deposits.reduce((previous, current) => {
                    return previous + parseFloat(current.amount);
                }, 0);
            },
            isLoading() {
                return this.loading < 2;
            }
        },

        methods: {
            loadBusinesses() {
                axios.get('/admin/businesses').then(response => this.businesses = response.data);
            },
            loadItems() {
                this.loading = 0;
                axios.get('/admin/deposits/pending/' + this.business_id + '?start_date=' + this.start_date + '&end_date=' + this.end_date)
                    .then(response => {
                        this.deposits = response.data.map(function(item) {
                            item.name = (item.deposit_type == 'business') ? item.business.name : item.caregiver.nameLastFirst;
                            return item;
                        });
                        this.loading++;
                    })
                    .catch(e => {
                        this.loading++;
                    });
                axios.get('/admin/deposits/missing_accounts/' + this.business_id)
                    .then(response => {
                        this.missingAccounts = response.data;
                        this.loading++;
                    })
                    .catch(e => {
                        this.loading++;
                    });
            },
            processDeposits()
            {
                if (this.business_id && confirm('Are you sure you wish to process the deposits for this business?')) {
                    this.processing = true;
                    let form = new Form({
                        start_date: this.start_date,
                        end_date: this.end_date,
                    });
                    form.post('/admin/deposits/pending/' + this.business_id).catch(error => { this.processing = false; })
                }
            }
        }
    }
</script>

<style>
    table:not(.form-check) {
        font-size: 13px;
    }
</style>
