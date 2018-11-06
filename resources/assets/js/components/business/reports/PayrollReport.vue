<template>
    <div>
        <b-card header="Select Date Range"
            header-text-variant="white"
            header-bg-variant="info"
        >
            <div class="form-inline">
                <date-picker
                    v-model="start_date"
                    placeholder="Start Date"
                    class="mb-2 mr-2"
                    :disabled="outstanding_filter == true"
                >
                </date-picker>
                <span class="mr-2">to</span>
                <date-picker
                    v-model="end_date"
                    placeholder="End Date"
                    class="mr-3 mb-2"
                    :disabled="outstanding_filter == true"
                >
                </date-picker>
                <span class="mr-3">or</span>
                <label class="custom-control custom-checkbox mb-2 mr-3">
                    <input type="checkbox" class="custom-control-input" name="outstanding_filter" v-model="outstanding_filter" value="1">
                    <span class="custom-control-indicator"></span>
                    <span class="custom-control-description">All Outstanding Payments Due</span>
                </label>
                <b-form-select
                    id="caregiver_id"
                    name="caregiver_id"
                    v-model="caregiver_id"
                    class="mr-3 mb-2"
                >
                    <option value="">All Caregivers</option>
                    <option v-for="caregiver in caregivers" :value="caregiver.id" :key="caregiver.id">{{ caregiver.name }}</option>
                </b-form-select>
                <b-button type="submit" variant="info" :disabled="busy" @click="fetch()">Generate Report</b-button>
            </div>
        </b-card>

        <loading-card v-if="busy" />

        <b-row v-if="! loaded && ! busy">
            <b-col lg="12">
                <b-card class="text-center text-muted">
                    Select filters and press Generate Report
                </b-card>
            </b-col>
        </b-row>

        <b-card v-if="loaded && ! busy"
            header="Results"
            header-text-variant="white"
            header-bg-variant="info"
        >
            <div class="table-responsive">
                <b-table bordered striped hover show-empty
                     :items="items"
                     :fields="fields"
                     :sort-by.sync="sortBy"
                     :sort-desc.sync="sortDesc"
                     :busy="busy"
                >
                    <template slot="caregiver_name" scope="row">
                        <a :href="`/business/caregivers/${row.item.caregiver_id}`">{{ row.item.caregiver_name }}</a>
                    </template>
                    <template slot="client_name" scope="row">
                        <a :href="`/business/clients/${row.item.client_id}`">{{ row.item.client_name }}</a>
                    </template>
                </b-table>
            </div>
        </b-card>
    </div>
</template>

<script>
    import FormatsDates from "../../../mixins/FormatsDates";
    import FormatsNumbers from "../../../mixins/FormatsNumbers";

    export default {
        mixins: [ FormatsDates, FormatsNumbers ],

        props: [ 'caregivers' ],

        data() {
            return {
                outstanding_filter: 0,
                end_date: moment(new Date()).format('MM/DD/YYYY'),
                start_date: moment(new Date()).subtract(14, 'days').format('MM/DD/YYYY'),
                caregiver_id: '',
                loaded: false,
                busy: false,
                items: [],
                fields: [
                    { label: 'Caregiver', key: 'caregiver_name', sortable: true },
                    { label: 'Pay Method', key: 'pay_method', sortable: true },
                    {
                        label: 'Payroll Rate',
                        key: 'caregiver_rate',
                        formatter: (val) => this.moneyFormat(val),
                        sortable: true
                    },
                    { label: 'Pay Regular Hours', key: 'hours_regular', sortable: true },
                    { label: 'Pay Overtime Hours', key: 'hours_overtime', sortable: true },
                    {
                        label: 'Total CG Pay',
                        key: 'caregiver_total',
                        formatter: (val) => this.moneyFormat(val),
                        sortable: true
                    },
                    {
                        label: 'Clock In',
                        key: 'checked_in_time',
                        formatter: (val) => this.formatDateTimeFromUTC(val),
                        sortable: true
                    },
                    {
                        label: 'Clock Out',
                        key: 'checked_out_time',
                        formatter: (val) => this.formatDateTimeFromUTC(val),
                        sortable: true
                    },
                    { label: 'Client', key: 'client_name', sortable: true },
                    {
                        label: 'Bill Total',
                        key: 'total',
                        formatter: (val) => this.moneyFormat(val),
                        sortable: true
                    },
                ],
                sortBy: 'checked_in_time',
                sortDesc: false,
            };
        },

        computed: {
            url() {
                if (this.outstanding_filter) {
                    return `/business/reports/payroll?json=1&caregiver=${this.caregiver_id}`;
                } else {
                    return `/business/reports/payroll?json=1&start=${this.start_date}&end=${this.end_date}&caregiver=${this.caregiver_id}`;
                }
            },
        },

        methods: {
            fetch() {
                this.busy = true;
                axios.get(this.url)
                    .then(response => {
                        this.items = response.data.map(function (item) {
                            return item;
                        });
                        this.loaded = true;
                        this.busy = false;
                    })
                    .catch(e => {
                        this.busy = false;
                    });
            },
        }
    }
</script>
