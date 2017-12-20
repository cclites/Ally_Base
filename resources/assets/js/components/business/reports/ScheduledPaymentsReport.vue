<template>
    <div>
        <b-row>
            <b-col lg="4">
                <dashboard-metric variant="info" :value="moneyFormat(reportTotals.selected)" :text="filterTitle" />
            </b-col>
            <b-col lg="4">
                <dashboard-metric variant="primary" :value="moneyFormat(reportTotals.scheduled)" text="Scheduled" />
            </b-col>
            <b-col lg="4">
                <dashboard-metric variant="success" :value="moneyFormat(reportTotals.year)" text="Year to Date" />
            </b-col>
        </b-row>
        <b-row>
            <b-col>
                <b-card
                        header="Filters"
                        header-text-variant="white"
                        header-bg-variant="info"
                >
                    <b-form inline @submit.prevent="reloadData()">
                        <date-picker
                                v-model="form.start_date"
                                placeholder="Start Date"
                        >
                        </date-picker>
                        &nbsp;to&nbsp;
                        <date-picker
                                v-model="form.end_date"
                                class="mr-2"
                                placeholder="End Date"
                        >
                        </date-picker>

                        <b-form-select v-model="form.caregiver" class="mr-2">
                            <option value="">All Caregivers</option>
                            <option :value="caregiver.id" v-for="caregiver in caregivers">{{ caregiver.nameLastFirst }}</option>
                        </b-form-select>

                        <b-form-select v-model="form.client">
                            <option value="">All Clients</option>
                            <option :value="client.id" v-for="client in clients">{{ client.nameLastFirst }}</option>
                        </b-form-select>

                        <b-button type="submit" variant="info" :disabled="fetchingData" class="ml-2">
                            Generate Report
                            <i class="fa fa-circle-o-notch fa-spin" v-if="fetchingData"></i>
                        </b-button>
                    </b-form>
                </b-card>
            </b-col>
        </b-row>
        <b-row>
            <b-col>
                <b-card>
                    <div class="table-responsive">
                        <b-table bordered striped hover show-empty
                                 :items="tableItems"
                                 :fields="fields"
                                 :current-page="currentPage"
                                 :per-page="perPage"
                                 :filter="filter"
                                 :sort-by.sync="sortBy"
                                 :sort-desc.sync="sortDesc"
                                 @filtered="onFiltered"
                        >
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
                </b-card>
            </b-col>
        </b-row>
    </div>
</template>

<script>
    import FormatsNumbers from '../../../mixins/FormatsNumbers'
    import FormatsDates from '../../../mixins/FormatsDates'

    export default {
        props: {
            'payments': {
                default() {
                    return [];
                }
            },
            'totals': {},
            'dates': {},
            'caregivers': Array,
            'clients': Array
        },

        mixins: [FormatsDates, FormatsNumbers],

        data() {
            return {
                fetchingData: false,
                form: {
                    start_date: this.formatDate(this.dates.start),
                    end_date: this.formatDate(this.dates.end),
                    caregiver: '',
                    client: ''
                },
                reportTotals: this.totals,
                totalRows: 0,
                perPage: 15,
                currentPage: 1,
                sortBy: null,
                sortDesc: false,
                editModalVisible: false,
                filter: null,
                modalDetails: { index:'', data:'' },
                selectedItem: {},
                items: this.payments,
                fields: [
                    {
                        key: 'shift_time',
                        label: 'Date',
                        sortable: true,
                        formatter: (value) => {
                            return this.formatDate(value);
                        }
                    },
                    {
                        key: 'shift_hours',
                        label: 'Hours',
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
                        key: 'business_allotment',
                        label: 'Provider Fee',
                        sortable: true,
                        formatter: (value) => {
                            return this.moneyFormat(value);
                        }
                    },
                    {
                        key: 'total_payment',
                        label: 'Estimated Total',
                        sortable: true,
                        formatter: (value) => {
                            return this.moneyFormat(value);
                        }
                    },
                    {
                        key: 'status',
                        label: 'Status',
                        sortable: true,
                        formatter: (value) => {
                            return _.startCase(_.lowerCase(value));
                        }
                    }
                ]
            }
        },

        mounted() {
            this.totalRows = this.items.length;
        },

        computed: {
            filterTitle() {
                return this.form.start_date + ' - ' + this.form.end_date;
            },

            tableItems() {
                return _.map(this.items, (item) => {
                    return {
                        shift_time: item.shift_time,
                        shift_hours: item.shift_hours,
                        client_name: item.client.name,
                        caregiver_name: item.caregiver.name,
                        business_allotment: item.business_allotment,
                        total_payment: item.total_payment,
                        status: item.status
                    }
                })
            }
        },

        methods: {
            reloadData() {
                this.fetchingData = true;
                let url = '/business/reports/scheduled_payments?start_date='+this.form.start_date+'&end_date='+this.form.end_date;
                if (_.isInteger(this.form.client)) {
                    url = url + '&client_id='+this.form.client
                }
                if (_.isInteger(this.form.caregiver)) {
                    url = url + '&caregiver_id='+this.form.caregiver
                }
                axios.get(url)
                    .then(response => {
                        this.items = response.data.payments;
                        this.reportTotals = response.data.totals;
                        this.fetchingData = false;
                    }).catch(error => {
                        console.error(error.response);
                        this.fetchingData = false;
                    });
            },

            details(item, index, button) {
                this.selectedItem = item;
                this.modalDetails.data = JSON.stringify(item, null, 2);
                this.modalDetails.index = index;
//                this.$root.$emit('bv::show::modal','caregiverEditModal', button);
                this.editModalVisible = true;
            },

            resetModal() {
                this.modalDetails.data = '';
                this.modalDetails.index = '';
            },

            onFiltered(filteredItems) {
                // Trigger pagination to update the number of buttons/pages due to filtering
                this.totalRows = filteredItems.length;
                this.currentPage = 1;
            }
        }
    }
</script>
