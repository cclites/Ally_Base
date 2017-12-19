<template>
    <b-card>
        <b-row>
            <b-col lg="12">
                <b-card header="Select Date Range"
                        header-text-variant="white"
                        header-bg-variant="info"
                >
                    <b-form inline>
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
                            <option value="">--Select a Business--</option>
                            <option v-for="business in businesses" :value="business.id">{{ business.name }}</option>
                        </b-form-select>
                        &nbsp;&nbsp;<b-button @click="loadItemsPerClient()" variant="info">Generate Per Client Report</b-button>
                        &nbsp;&nbsp;<b-button @click="loadItems()" variant="primary">Generate Actual Report</b-button>
                        &nbsp;&nbsp;<b-button @click="processCharges()" variant="danger" :disabled="processing">Process Charges</b-button>
                    </b-form>
                </b-card>
            </b-col>
        </b-row>
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
                start_date: moment().startOf('isoweek').subtract(7, 'days').format('MM/DD/YYYY'),
                end_date: moment().startOf('isoweek').subtract(1, 'days').format('MM/DD/YYYY'),
                business_id: "",
                businesses: [],
                processing: false,
                charges: [],
                fields: [
                    {
                        key: 'client_id',
                        label: 'Client ID',
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
                        formatter: this.numberFormat
                    },
                    {
                        key: 'caregiver_allotment',
                        label: 'Caregiver Allotment',
                        sortable: true,
                        formatter: this.numberFormat
                    },
                    {
                        key: 'business_allotment',
                        label: 'Business Allotment',
                        sortable: true,
                        formatter: this.numberFormat
                    },
                    {
                        key: 'system_allotment',
                        label: 'Ally Allotment',
                        sortable: true,
                        formatter: this.numberFormat
                    },
                    {
                        key: 'payment_type',
                        label: 'Type',
                        sortable: true,
                    },
                    {
                        key: 'total_shifts',
                        label: 'Total Shifts',
                        sortable: true,
                    },
                    {
                        key: 'unauthorized_shifts',
                        label: 'Unauthorized',
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
                return this.charges.map(item => {
                    item.name = (item.business) ? item.business.name : item.client.nameLastFirst;
                    return item;
                })
            },
            totalItems() {
                return this.charges.length;
            },
            totalAmount() {
                return this.charges.reduce((previous, current) => {
                    return previous + parseFloat(current.amount);
                }, 0);
            }
        },

        methods: {
            loadBusinesses() {
                axios.get('/admin/businesses').then(response => this.businesses = response.data);
            },
            loadItems() {
                axios.get('/admin/charges/pending/' + this.business_id + '?start_date=' + this.start_date + '&end_date=' + this.end_date)
                    .then(response => {
                        this.charges = response.data;
                    });
            },
            loadItemsPerClient() {
                axios.get('/admin/charges/pending/' + this.business_id + '/per-client?start_date=' + this.start_date + '&end_date=' + this.end_date)
                    .then(response => {
                        this.charges = response.data;
                    });
            },
            processCharges()
            {
                if (this.business_id && confirm('Are you sure you wish to process the charges for this business?')) {
                    this.processing = true;
                    let form = new Form({
                        start_date: this.start_date,
                        end_date: this.end_date,
                    });
                    form.post('/admin/charges/pending/' + this.business_id).catch(error => { this.processing = false; })
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
