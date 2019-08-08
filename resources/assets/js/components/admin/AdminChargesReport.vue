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
                            >
                            <option value="">--All Providers--</option>
                            <option v-for="business in businesses" :value="business.id" :key="business.id">{{ business.name }}</option>
                        </b-form-select>
                        &nbsp;&nbsp;<b-button type="submit" variant="info">Generate Report</b-button>
                    </b-form>
                </b-card>
            </b-col>
        </b-row>
        <b-row>
            <b-col>
                <strong>Successful Charges: </strong> {{ moneyFormat(successfulTotal) }} &nbsp;
                <strong>Failed Charges: </strong> {{ moneyFormat(failedTotal) }} &nbsp;
                <strong>Overall: </strong> {{ moneyFormat(overallTotal) }}
            </b-col>
            <b-col class="text-right">
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
                <template slot="status" scope="data">
                    <span style="color: red; font-weight: bold" v-if="data.value == 'failed'">{{ data.value }}</span>
                    <span style="color: darkgreen" v-else>{{ data.value }}</span>
                </template>
                <template slot="success" scope="data">
                    <span style="color: red; font-weight: bold" v-if="data.value == 0">No</span>
                    <span style="color: darkgreen" v-else>Yes</span>
                </template>
                <template slot="actions" scope="row">
                    <b-btn size="sm" :href="'/admin/transactions/' + row.item.transaction_id"  v-if="row.item.transaction_id">View Transaction</b-btn>
                    <b-btn size="sm" @click="markFailed(row.item)" variant="success" v-if="row.item.success">Mark Failed</b-btn>
                    <b-btn size="sm" @click="refundInit(row.item)" variant="primary" v-if="row.item.success">Refund</b-btn>
                </template>
            </b-table>
        </div>

        <b-modal id="refundModal" :title="`Refund ${refundCharge.name}`" v-model="refundModal">
            <b-container fluid>
                <form @keydown="refundForm.clearError($event.target.name)">
                    <b-row>
                        <b-col lg="12">
                            <b-form-group label="Original Amount" label-for="amount">
                                <b-form-input
                                        type="number"
                                        step="any"
                                        :value="refundCharge.amount"
                                        disabled
                                >
                                </b-form-input>
                                <input-help :form="refundForm" field="aaaaaaa" text="The original transaction amount for reference."></input-help>
                            </b-form-group>
                            <b-form-group label="Refund Amount" label-for="amount">
                                <b-form-input
                                        id="amount"
                                        type="number"
                                        step="any"
                                        name="amount"
                                        v-model="refundForm.amount"
                                >
                                </b-form-input>
                                <input-help :form="refundForm" field="amount" text="Enter the amount of the refund. (Required)"></input-help>
                            </b-form-group>
                            <b-form-group label="Refund Notes" label-for="notes">
                                <b-form-textarea
                                        id="notes"
                                        name="notes"
                                        :rows="3"
                                        v-model="refundForm.notes"
                                >
                                </b-form-textarea>
                                <input-help :form="refundForm" field="notes" text="Enter the adjustment notes to display to the recipient. (Required)"></input-help>
                            </b-form-group>
                        </b-col>
                    </b-row>
                </form>
            </b-container>
            <div slot="modal-footer">
                <b-btn variant="default" @click="refundModal = false">Close</b-btn>
                <b-btn variant="info" @click="submitRefund()">Refund</b-btn>
            </div>
        </b-modal>
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
                start_date: moment().startOf('isoweek').format('MM/DD/YYYY'),
                end_date: moment().startOf('isoweek').add(6, 'days').format('MM/DD/YYYY'),
                business_id: "",
                businesses: [],
                items: [],
                loading: false,
                fields: [
                    {
                        key: 'id',
                        label: 'Payment ID',
                        sortable: true,
                    },
                    {
                        key: 'name',
                        label: 'Name',
                        sortable: true,
                    },
                    {
                        key: 'amount',
                        label: 'Total Amount',
                        sortable: true,
                    },
                    {
                        key: 'business_name',
                        label: 'Registry',
                        sortable: true,
                    },
                    {
                        key: 'created_at',
                        label: 'Date',
                        sortable: true,
                    },
                    {
                        key: 'gateway_id',
                        label: 'Transaction ID',
                        sortable: true,
                    },
                    {
                        key: 'transaction_response',
                        label: 'Trans. Response',
                        sortable: true,
                    },
                    {
                        key: 'status',
                        label: 'Last Status',
                        sortable: true,
                    },
                    {
                        key: 'success',
                        label: 'Successful',
                        sortable: true,
                    },
                    'actions'
                ],
                refundModal: false,
                refundForm: new Form({}),
                refundCharge: {},
            }
        },

        computed: {
            successfulTotal() {
                return this.items.reduce(function(carry, item) {
                    if (!item.success) return carry;
                    return carry + parseFloat(item.amount);
                }, 0);
            },
            failedTotal() {
                return this.items.reduce(function(carry, item) {
                    if (item.success) return carry;
                    return carry + parseFloat(item.amount);
                }, 0);
            },
            overallTotal() {
                return this.items.reduce(function(carry, item) {
                    return carry + parseFloat(item.amount);
                }, 0);
            }
        },

        mounted() {
            this.refundInit(); // initialize
            this.loadBusinesses();
            //this.loadItems();
        },

        methods: {
            loadBusinesses() {
                axios.get('/admin/businesses').then(response => this.businesses = response.data);
            },
            loadItems() {
                this.loading = true;
                axios.get('/admin/charges?json=1&business_id=' + this.business_id + '&start_date=' + this.start_date + '&end_date=' + this.end_date)
                    .then(response => {

                        this.items = response.data.map(function(item) {
                            item.name = (item.client) ? item.client.nameLastFirst : '';
                            item.business_name = (item.business) ? item.business.name : '';
                            item.transaction_response = (item.transaction) ? item.transaction.response_text : '';
                            item.gateway_id = (item.transaction) ? item.transaction.transaction_id : '';
                            item.status = (item.transaction && item.transaction.last_history) ? item.transaction.last_history.status : '';

                            return item;
                        });
                        this.loading = false;
                    })
                    .catch(e => {
                        this.loading = false;
                    });
            },
            markSuccessful(charge) {
                if (!confirm('Are you sure you wish to mark the charge of ' + charge.amount + ' for ' + charge.name + ' as SUCCESSFUL?')) {
                    return;
                }
                let form = new Form();
                form.post('/admin/charges/successful/' + charge.id)
                    .then(response => {
                        charge.success = true;
                    });
            },
            markFailed(charge) {
                if (!confirm('Are you sure you wish to mark the charge of ' + charge.amount + ' for ' + charge.name + ' as FAILED?  Note: This will also place this entity on hold.')) {
                    return;
                }
                let form = new Form();
                form.post('/admin/charges/failed/' + charge.id)
                    .then(response => {
                        charge.success = false;
                    });
            },
            refundInit(charge = null) {
                this.refundCharge = charge ? charge : {};
                this.refundForm = new Form({
                    amount: '0.00',
                    notes: '',
                });
                if (charge) {
                    this.refundModal = true;
                }
            },
            async submitRefund() {
                let url = '/admin/transactions/refund/' + this.refundCharge.transaction_id;
                try {
                    await this.refundForm.post(url);
                    this.refundModal = false;
                }
                catch (e) {
                    console.log(e);
                    if (e.status === 500) this.refundModal = false;
                }
            }
        }
    }
</script>

<style>
    table:not(.form-check) {
        font-size: 14px;
    }
</style>
