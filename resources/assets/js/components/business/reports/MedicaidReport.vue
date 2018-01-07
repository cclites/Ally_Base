<template>
    <b-container fluid>
        <b-row>
            <b-col>
                <b-card
                        header="Select Date Range"
                        header-text-variant="white"
                        header-bg-variant="info">
                    <b-form inline @submit.prevent="reloadData()">
                        <date-picker
                                v-model="form.start_date"
                                placeholder="Start Date"
                                weekStart="1">
                        </date-picker> &nbsp;to&nbsp;
                        <date-picker
                                v-model="form.end_date"
                                placeholder="End Date">
                        </date-picker>
                        &nbsp;&nbsp;
                        <b-button type="submit" variant="info" :disabled="fetchingData">
                            Generate Report
                            <i class="fa fa-circle-o-notch fa-spin" v-if="fetchingData"></i>
                        </b-button>
                    </b-form>
                </b-card>
            </b-col>
        </b-row>
        <b-row>
            <b-col lg="4">
                <dashboard-metric variant="info" :value="reportTotals.hours" text="Total Hours"/>
            </b-col>
            <b-col lg="4">
                <dashboard-metric variant="primary" :value="moneyFormat(reportTotals.ally_fee)" text="Total Ally Fee"/>
            </b-col>
            <b-col lg="4">
                <dashboard-metric variant="success" :value="moneyFormat(reportTotals.owed)"
                                  text="Total Owed by Provider"/>
            </b-col>
        </b-row>
        <b-row>
            <b-col>
                <b-card>
                    <div class="table-responsive">
                        <b-table bordered striped hover show-empty
                                 :items="items"
                                 :fields="fields"
                                 :current-page="currentPage"
                                 :per-page="perPage"
                                 :filter="filter"
                                 :sort-by.sync="sortBy"
                                 :sort-desc.sync="sortDesc"
                                 :empty-text="emptyText"
                                 @filtered="onFiltered">
                            <template slot="wages" slot-scope="data">
                                {{ moneyFormat(data.item.duration * data.item.caregiver_rate) }}
                            </template>
                            <template slot="actions" scope="row">

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
                </b-card>
            </b-col>
        </b-row>
    </b-container>
</template>

<script>
    import FormatsNumbers from '../../../mixins/FormatsNumbers';
    import FormatsDates from '../../../mixins/FormatsDates';

    export default {
        props: {
            'shifts': {
                default() {
                    return [];
                }
            },
            'totals': {
                default() {
                    return {};
                }
            },
            'dates': {}
        },

        mixins: [FormatsNumbers, FormatsDates],

        data() {
            return {
                emptyText: 'No records for ' + this.formatDate(this.dates.start.date) + ' through ' + this.formatDate(this.dates.end.date),
                form: {
                    start_date: moment(this.dates.start.date).format('MM/DD/YYYY'),
                    end_date: moment(this.dates.end.date).format('MM/DD/YYYY'),
                },
                fetchingData: false,
                reportTotals: this.totals,
                totalRows: 0,
                perPage: 30,
                currentPage: 1,
                sortBy: null,
                sortDesc: false,
                filter: null,
                modalDetails: {index: '', data: ''},
                selectedItem: {},
                fields: [
                    {
                        key: 'checked_in_time',
                        label: 'Date',
                        sortable: true,
                        formatter: (value) => {
                            return this.formatDate(value);
                        }

                    },
                    {
                        key: 'caregiver_name',
                        label: 'Caregiver',
                        sortable: true
                    },
                    {
                        key: 'hours',
                        label: 'Hours',
                        sortable: true,
                    },
                    {
                        key: 'wages',
                        label: 'Wages',
                        sortable: true,
                    },
                    {
                        key: 'provider_fee',
                        label: 'Provider Fee',
                        sortable: true,
                        formatter: (value) => {
                            return this.moneyFormat(value);
                        }
                    },
                    {
                        key: 'ally_fee',
                        label: 'Ally Fee',
                        sortable: true,
                        formatter: (value) => {
                            return this.moneyFormat(value);
                        }
                    }
                ],
                items: this.shifts,
            }
        },

        mounted() {
            this.totalRows = this.items.length;
        },

        methods: {
            reloadData() {
                this.fetchingData = true;
                axios.post('/business/reports/medicaid', { start_date: this.form.start_date, end_date: this.form.end_date })
                    .then(response => {
                        this.reportTotals = response.data.totals;
                        this.items = response.data.shifts;
                        this.fetchingData = false;
                        this.emptyText = 'No records for ' + this.formatDate(this.form.start_date) + ' through ' + this.formatDate(this.form.end_date);
                        if (this.items.length === 0) {
                            alerts.addMessage('warning', 'No results for ' + this.formatDate(this.form.start_date) + ' through ' + this.formatDate(this.form.end_date) + ' ');
                        }
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
