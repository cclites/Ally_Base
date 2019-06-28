<template>
    <b-card>
        <b-row>
            <b-col lg="9">
                <b-card header="Select Date Range"
                        header-text-variant="white"
                        header-bg-variant="info"
                >
                    <b-form inline @submit.prevent="loadItemsWithoutShift()">
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
            <b-col lg="3">
                <b-card header="Shift Search"
                        header-text-variant="white"
                        header-bg-variant="info"
                >
                    <b-form inline @submit.prevent="loadItems()">
                        <b-form-group label="Shift ID: " label-for="shiftId">
                            <b-form-input type="text"
                                          id="shiftId"
                                          v-model="shiftId"
                            />
                            <b-btn type="submit" variant="info">Get Related Deposits</b-btn>
                        </b-form-group>
                    </b-form>
                </b-card>
            </b-col>

        </b-row>
        <b-row v-if="shiftMsg">
            <b-col>
                <h4>{{ this.shiftMsg }}</h4>
            </b-col>
        </b-row>

        <loading-card v-show="loading"></loading-card>
        
        <div v-if="! loading">
            <b-row>
                <b-col class="text-right">
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
                    <template slot="id" scope="row">
                        <a :href="`/admin/deposits/${row.item.id}/`" target="_blank">{{ row.item.id }}</a>
                    </template>
                    <template slot="status" scope="data">
                        <span style="color: red; font-weight: bold" v-if="data.value == 'failed'">{{ data.value }}</span>
                        <span style="color: darkgreen" v-else>{{ data.value }}</span>
                    </template>
                    <template slot="success" scope="data">
                        <span style="color: red; font-weight: bold" v-if="data.value == 0">No</span>
                        <span style="color: darkgreen" v-else>Yes</span>
                    </template>
                    <template slot="actions" scope="row">
                        <b-btn size="sm" :href="'/admin/transactions/' + row.item.transaction_id" v-if="row.item.transaction_id">View Transaction</b-btn>
                        <b-btn size="sm" @click="markFailed(row.item)" variant="success" v-if="row.item.success">Mark Failed</b-btn>
                    </template>
                </b-table>
            </div>
        </div>
    </b-card>
</template>

<script>
    export default {

        props: {
            'initialShiftId': String,
        },

        data() {
            return {
                sortBy: null,
                sortDesc: false,
                filter: null,
                start_date: moment().startOf('isoweek').format('MM/DD/YYYY'),
                end_date: moment().startOf('isoweek').add(6, 'days').format('MM/DD/YYYY'),
                business_id: "",
                businesses: [],
                shiftId: this.initialShiftId,
                shiftMsg: "",
                items: [],
                loading: false,
                fields: [
                    {
                        key: 'id',
                        label: 'Deposit ID',
                        sortable: true,
                    },
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
                        label: 'Initial Response',
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
                    'actions',
                ]
            }
        },

        mounted() {
            this.loadBusinesses();
            this.loadItems();
        },

        methods: {
            loadBusinesses() {
                axios.get('/admin/businesses').then(response => this.businesses = response.data);
            },
            loadItems() {
                this.loading = true;
                let shiftId = this.shiftId;
                axios.get('/admin/deposits?json=1&shift_id=' + shiftId + '&business_id=' + this.business_id + '&start_date=' + this.start_date + '&end_date=' + this.end_date)
                    .then(response => {
                        this.items = response.data.map(item => {
                            item.name = (item.deposit_type == 'business') ? item.business.name : item.caregiver.nameLastFirst;
                            item.transaction_response = (item.transaction) ? item.transaction.response_text : '';
                            item.gateway_id = (item.transaction) ? item.transaction.transaction_id : '';
                            item.status = (item.transaction && item.transaction.last_history) ? item.transaction.last_history.status : '';
                            return item;
                        });
                        this.shiftMsg = "";
                        if (shiftId) {
                            this.shiftMsg = "Below are deposits relating ONLY to shift ID " + shiftId;
                        }
                        this.loading = false;
                    })
                    .catch(e => {
                        this.loading = false;
                    });
            },
            loadItemsWithoutShift() {
                this.shiftId = "";
                return this.loadItems();
            },
            markSuccessful(deposit) {
                if (!confirm('Are you sure you wish to mark the deposit of ' + deposit.amount + ' for ' + deposit.name + ' as SUCCESSFUL?')) {
                    return;
                }
                let form = new Form();
                form.post('/admin/deposits/successful/' + deposit.id)
                    .then(response => {
                        deposit.success = true;
                    });
            },
            markFailed(deposit) {
                if (!confirm('Are you sure you wish to mark the deposit of ' + deposit.amount + ' for ' + deposit.name + ' as FAILED?  Note: This will also place this entity on hold.')) {
                    return;
                }
                let form = new Form();
                form.post('/admin/deposits/failed/' + deposit.id)
                    .then(response => {
                        deposit.success = false;
                    });
            }
        }
    }
</script>

<style>
    table:not(.form-check) {
        font-size: 14px;
    }
</style>
