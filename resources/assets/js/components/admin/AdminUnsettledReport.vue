<template>
    <b-card>
        <b-row>
            <b-col lg="12">
                <b-card 
                    header="Select Date Range"
                    header-text-variant="white"
                    header-bg-variant="info"
                >
                    <b-form inline @submit.prevent="loadItems()">
                        <date-picker
                            v-model="start_date"
                            placeholder="Start Date"
                            class="mt-1"
                        >
                        </date-picker> &nbsp;to&nbsp;
                        <date-picker
                            v-model="end_date"
                            placeholder="End Date"
                            class="mt-1"
                        >
                        </date-picker>&nbsp;
                        <b-form-select
                            id="business_id"
                            name="business_id"
                            v-model="business_id"
                            class="mt-1"
                        >
                            <option value="">-- All Businesses</option>
                            <option v-for="business in businesses" :value="business.id" :key="business.id">{{ business.name }}</option>
                        </b-form-select>
                        <b-form-select
                            id="client_id"
                            name="client_id"
                            v-model="client_id"
                            class="mt-1"
                        >
                            <option value="">-- All Clients</option>
                            <option v-for="client in clients" :value="client.id" :key="client.id">{{ client.name }}</option>
                        </b-form-select>
                        <b-form-select
                            id="caregiver_id"
                            name="caregiver_id"
                            v-model="caregiver_id"
                            class="mt-1"
                        >
                            <option value="">-- All Carevigers</option>
                            <option v-for="caregiver in caregivers" :value="caregiver.id" :key="caregiver.id">{{ caregiver.name }}</option>
                        </b-form-select>
                        <span class="d-none d-sm-inline">&nbsp;</span>
                        <b-button 
                            @click="toggle_statuses = !toggle_statuses" 
                            variant="secondary"
                            class="mt-1"
                        >
                            Toggle Statuses
                        </b-button>&nbsp;
                        <b-button 
                            type="submit" 
                            variant="info"
                            class="mt-1"
                        >
                            Generate Report
                        </b-button>&nbsp;
                    </b-form>
                    <b-form-group v-show="toggle_statuses" label="Toggle Statuses" style="text-transform: capitalize;">
                        <b-form-checkbox-group stacked v-model="selected_statuses" name="selected_statuses" :options="statuses">
                        </b-form-checkbox-group>
                    </b-form-group>
                </b-card>
            </b-col>
        </b-row>

        <loading-card v-show="loading"></loading-card>

        <div v-if="! loading">
            <div class="table-responsive">
                <b-table bordered striped hover show-empty
                    :items="items"
                    :fields="fields"
                    :sort-by.sync="sortBy"
                    :sort-desc.sync="sortDesc"
                    :per-page="perPage"
                    :current-page="currentPage"
                >
                    <template slot="checked_in_time" scope="data">
                        {{ dayFormat(data.value) }}
                    </template>
                    <template slot="business_id" scope="{value:id}">
                        {{ provider(id).name }}
                    </template>
                    <template slot="EVV" scope="data">
                        <span v-if="data.value" style="color: green">
                            <i class="fa fa-check-square-o"></i>
                        </span>
                        <span v-else style="color: darkred">
                            <i class="fa fa-times-rectangle-o"></i>
                        </span>
                    </template>
                    <template slot="bottom-row" scope="data">
                        <th colspan="5" class="text-right">Totals</th>
                        <th>{{ sum('hours') }}</th>
                        <th>{{ sum('mileage_costs') }}</th>
                        <th>{{ sum('shift_total') }}</th>
                        <th>{{ sum('caregiver_total') }}</th>
                        <th>{{ sum('provider_total') }}</th>
                        <th>{{ sum('ally_total') }}</th>
                        <th colspan="2"></th>
                    </template>
                    <template slot="actions" scope="data">
                        <b-btn variant="info" :href="`/business/shifts/${data.item.id}`" size="sm">
                            <i class="fa fa-edit"></i>
                        </b-btn>
                    </template>
                </b-table>
            </div>
            <b-row>
                <b-col lg="6">
                    <b-pagination :total-rows="totalRows" :per-page="perPage" v-model="currentPage"/>
                </b-col>
                <b-col lg="6" class="text-right">
                    Showing {{ perPage < totalRows ? perPage : totalRows }} of {{ totalRows }} results
                </b-col>
            </b-row>
        </div>
    </b-card>
</template>

<script>
    import FormatsNumbers from '../../mixins/FormatsNumbers';
    import FormatsDates from '../../mixins/FormatsDates';

    export default {
        mixins: [FormatsNumbers, FormatsDates],

        props: {
            start_at: {default: null},
            end_at: {default: null},
            client_id: {default: ''},
            business_id: {default: ''},
            caregiver_id: {default: ''},
            selected_statuses: {default: []}
        },

        data() {
            return {
                totalRows: 0,
                perPage: 20,
                currentPage: 1,
                sortBy: 'shift_time',
                sortDesc: false,
                start_date: this.start_at,
                end_date: this.end_at,
                businesses: [],
                clients: [],
                caregivers: [],
                toggle_statuses: false,
                statuses: [],
                loading: false,
                items: [],
                fields: [
                    {
                        key: 'checked_in_time',
                        label: 'Date',
                        sortable: true,
                    },
                    {
                        key: 'status',
                        sortable: true,
                    },
                    {
                        key: 'client_name',
                        label: 'Client',
                        sortable: true,
                    },
                    {
                        key: 'caregiver_name',
                        label: 'Caregiver',
                        sortable: true,
                    },
                    {
                        key: 'business_id',
                        label: 'Business',
                        sortable: true,
                    },
                    {
                        key: 'hours',
                        label: 'Hours',
                        sortable: true,
                    },
                    {
                        key: 'mileage_costs',
                        label: 'Mileage Cost',
                        sortable: true,
                    },
                    {
                        key: 'shift_total',
                        label: 'Amount',
                        sortable: true,
                    },
                    {
                        key: 'caregiver_total',
                        label: 'Caregiver Allotment',
                        sortable: true,
                    },
                    {
                        key: 'provider_total',
                        label: 'Business Allotment',
                        sortable: true,
                    },
                    {
                        key: 'ally_total',
                        label: 'Ally Allotment',
                        sortable: true,
                    },
                    {
                        key: 'created_at',
                        label: 'Added',
                        sortable: true,
                        formatter: ts => ts ? this.formatDateTimeFromUTC(ts) : '-'
                    },
                    {
                        key: 'confirmed_at',
                        label: 'Confirmed',
                        sortable: true,
                        formatter: ts => ts ? this.formatDateTimeFromUTC(ts) : '-'
                    },
                    {
                        key: 'actions',
                        label: '',
                        sortable: false,
                    },
                    // {
                    //     key: 'ally_pct',
                    //     label: 'Ally %',
                    //     sortable: true,
                    //     formatter: this.percentageFormat
                    // },
                    // {
                    //     key: 'EVV',
                    //     label: 'EVV Verified',
                    //     sortable: true,
                    // }
                ]
            }
        },

        beforeMount() {
            if (! moment(this.start_date).isValid() ) {
                this.start_date = moment().startOf('isoweek').subtract(7, 'days').format('MM/DD/YYYY')
            }
            if (! moment(this.end_date).isValid() ) {
                this.end_date = moment().startOf('isoweek').subtract(1, 'days').format('MM/DD/YYYY')
            }
        },

        mounted() {
            this.loadFilters();
        },

        methods: {
            sum(prop) {
                let total = 0;
                total = this.items.reduce((sum, item) => {                     
                    let parsed = parseFloat(
                        // remove commas
                        item[prop].replace(/,/g, '')
                    )
                    
                    return sum + parsed
                }, total);
                
                return this.numberFormat(total);
            },
            
            provider(id) {
                return Object.assign(
                    { name:'N/A' }, 
                    this.businesses.find(b => b.id == id)
                );
            },
            
            authorizeAll() {
                let form = new Form({
                   start_date: this.start_date,
                   end_date: this.end_date,
                   business_id: this.business_id,
                   authorized: 1
                });
                form.post('/admin/charges/pending_shifts');
            },
            
            loadFilters() {
                axios.get('/admin/businesses').then(response => this.businesses = response.data);
                axios.get('/admin/clients').then(response => this.clients = response.data);
                axios.get('/admin/caregivers').then(response => this.caregivers = response.data);
                axios.get('/admin/reports/unsettled/statuses').then(response => {
                    this.statuses = response.data.map(status => ({
                        text: status.replace(/\_/g,' ').toLowerCase(),
                        value: status
                    }));
                });
            },
            
            loadItems() {
                this.loading = true;

                let params = {
                    start_date: this.start_date,
                    end_date: this.end_date,
                    status: this.selected_statuses
                }
                
                if (this.business_id) params.business_id = this.business_id;
                if (this.caregiver_id) params.caregiver_id = this.caregiver_id;
                if (this.client_id) params.client_id = this.client_id;
                
                axios.get('/admin/reports/unsettled/data', {params})
                    .then(response => {
                        this.items = response.data;
                        this.totalRows = response.data.length;
                        this.loading = false;
                    })
                    .catch(e => {
                        this.loading = false;
                    });
            },

            dayFormat(date) {
                //return this.formatDateFromUTC(date)
                return moment(date).local().format('ddd MMM D');
            },
        }
    }
</script>

<style>
    table:not(.form-check) {
        font-size: 13px;
    }
    .form-inline .custom-controls-stacked .custom-control {
        justify-content: left;
    }
</style>
