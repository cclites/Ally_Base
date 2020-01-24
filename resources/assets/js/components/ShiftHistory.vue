<template>
    <div>
        <b-card>
            <b-row>
                <b-col lg="12">
                    <b-card
                            header="Select Date Range &amp; Filters"
                            header-text-variant="white"
                            header-bg-variant="info"
                    >
                        <b-form inline @submit.prevent="reloadData()">
                            <date-picker
                                    class="mb-1"
                                    v-model="start_date"
                                    placeholder="Start Date">
                            </date-picker> &nbsp;to&nbsp;
                            <date-picker
                                    class="mb-1"
                                    v-model="end_date"
                                    placeholder="End Date">
                            </date-picker>
                            <b-form-select v-model="client_id" class="mx-1 mb-1">
                                <option value="">All Clients</option>
                                <option v-for="item in clients" :value="item.id" :key="item.id">{{ item.name }}</option>
                            </b-form-select>
                            <b-button type="submit" variant="info" class="mb-1">Generate Report</b-button>
                        </b-form>
                    </b-card>
                </b-col>
            </b-row>

            <loading-card v-show="loading"></loading-card>

            <div v-if="! loading" class="table-responsive">
                <b-table bordered striped hover show-empty
                        :items="items"
                        :fields="fields"
                        :current-page="currentPage"
                        :per-page="perPage"
                        :filter="filter"
                        :sort-by.sync="sortBy"
                        :sort-desc.sync="sortDesc"
                        @filtered="onFiltered"
                >
                    <template slot="actions" scope="row">
                        <b-btn size="sm" @click.stop="details(row.item)" v-b-tooltip.hover title="View">
                            <i class="fa fa-eye"></i>
                        </b-btn>
                    </template>
                </b-table>
            </div>

            <b-row>
                <b-col lg="6" >
                    <b-pagination :total-rows="totalRows" :per-page="perPage" v-model="currentPage" />
                </b-col>
                <b-col lg="6" class="text-right">
                    Showing {{ perPage < totalRows ? perPage : totalRows }} of {{ totalRows }} results
                </b-col>
            </b-row>
        </b-card>

        <!-- Details modal -->
        <shift-details-modal v-model="detailsModal" :shift="selectedItem"></shift-details-modal>
    </div>
</template>

<script>
    import FormatsDates from '../mixins/FormatsDates';
    import FormatsNumbers from '../mixins/FormatsNumbers';
    import ShiftDetailsModal from "./modals/ShiftDetailsModal";

    export default {
        components: {
            ShiftDetailsModal,
        },

        props: {
            'clients': {
                default() {
                    return [];
                }
            },
        },

        mixins: [FormatsDates, FormatsNumbers],

        data() {
            return {
                items: [],
                totalRows: 0,
                perPage: 15,
                currentPage: 1,
                sortBy: null,
                sortDesc: false,
                editModalVisible: false,
                filter: null,
                modalDetails: { index:'', data:'' },
                selectedItem: {
                    client: {}
                },
                fields: [
                    {
                        key: 'client_name',
                        label: 'Client',
                        sortable: true
                    },
                    {
                        key: 'checked_in_time',
                        label: 'Clocked In',
                        formatter: (value) => { return this.formatDateTimeFromUTC(value); },
                        sortable: true
                    },
                    {
                        key: 'checked_out_time',
                        label: 'Clocked Out',
                        formatter: (value) => { return this.formatDateTimeFromUTC(value); },
                        sortable: true
                    },
                    {
                        key: 'hours',
                        label: 'Hours',
                        sortable: true
                    },
                    {
                        key: 'caregiver_rate',
                        label: 'Rate',
                        formatter: (value) => { return this.moneyFormat(value) },
                        sortable: true
                    },
                    {
                        class: ['d-none', 'd-sm-none', 'd-md-table-cell', 'd-lg-table-cell', 'd-xl-table-cell'],
                        key: 'verified',
                        label: 'Verified',
                        sortable: true,
                        formatter: (value) => { return value ? 'Verified' : 'Unverified'; }
                    },
                    {
                        key: 'admin_note',
                        class: 'hidden-print',
                        label: 'Admin Note',
                    },
                    {
                        key: 'actions',
                        class: 'hidden-print',
                        label: ' ',
                    },
                ],
                client_id: '',
                start_date: moment().startOf('month').format('MM/DD/YYYY'),
                end_date: moment().endOf('month').format('MM/DD/YYYY'),
                loading: true,
                detailsModal: false,
            }
        },

        mounted() {
            this.reloadData();
        },

        computed: {
            queryString() {
                return `?client_id=${this.client_id}&start_date=${this.start_date}&end_date=${this.end_date}`;
            },
        },

        methods: {
            reloadData() {
                this.loadData();
            },
            
            async loadData() {
                this.loading = true;

                let form = new Form({});
                try {
                    const response = await form.get('/reports/shifts' + this.queryString);
                    if (Array.isArray(response.data)) {
                        this.items = response.data;
                    }
                    else {
                        this.items = [];
                    }
                }
                catch (e) {
                    this.items = [];
                }
                this.totalRows = this.items.length;
                this.loading = false;
            },

            details(item) {
                let component = this;
                axios.get('/shifts/' + item.id)
                    .then(function(response) {
                        let shift = response.data;
                        shift.checked_in_time = moment.utc(shift.checked_in_time).local().format('L LT');
                        shift.checked_out_time = moment.utc(shift.checked_out_time).local().format('L LT');
                        component.selectedItem = shift;
                        component.detailsModal = true;
                        console.log(component.selectedItem);
                    })
                    .catch(function(error) {
                        alert('Error loading shift details');
                    });
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
