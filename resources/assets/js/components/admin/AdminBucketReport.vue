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
                        &nbsp;&nbsp;
                        <b-button @click="loadItems()" variant="info" :disabled="processing">
                            <i class="fa fa-spinner fa-spin" v-show="processing"></i> Generate Bucket Report
                        </b-button>
                    </b-form>
                </b-card>
            </b-col>
        </b-row>
        <b-row>
            <b-col sm="12">
                <b>This date range includes {{ totalItems }} deposits for a total amount of {{ numberFormat(totalAmount) }}.</b>
            </b-col>
        </b-row>
        <div class="table-responsive">
            <b-table bordered striped hover show-empty
                     :items="items"
                     :fields="fields"
                     :sort-by.sync="sortBy"
                     :sort-desc.sync="sortDesc"
            >
                <template slot="show_details" scope="row">
                    <b-btn @click.stop="row.toggleDetails">
                        Payment Breakdown
                    </b-btn>
                </template>
                <template slot="row-details" scope="row">
                    <div class="table-responsive">
                        <table class="table table-condensed">
                            <thead>
                            <tr>
                                <th>Date (or Status)</th>
                                <th>Deposited Amount</th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="(value,date) in row.item.payment_dates">
                                <td>{{ date }}</td>
                                <td>{{ numberFormat(value) }}</td>
                                <td>
                                    <b-btn v-if="date === 'missing' || date === 'failed'" @click="toggleDate(row.item[date])" size="sm">View {{ date }} shifts</b-btn>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </template>
            </b-table>
        </div>

        <b-modal id="dateShiftsModal" title="Shift List Drilldown" v-model="dateShiftsModal" size="lg">
            <b-container fluid>
                <table class="table table-bordered">
                    <thead>
                    <tr>
                        <th>Date</th>
                        <th>Hours</th>
                        <th>Client</th>
                        <th>View</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="shift in dateShifts">
                        <td>{{ formatDateTimeFromUTC(shift.checked_in_time) }}</td>
                        <td>{{ numberFormat(shift.duration) }}</td>
                        <td>{{ shift.client.name }}</td>
                        <td>
                            <b-btn @click="viewShift(shift)">View</b-btn>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </b-container>
        </b-modal>

        <!-- Details modal -->
        <shift-details-modal v-model="detailsModal" :selected-item="selectedItem">
            <template slot="buttons" scope="row">
                <b-btn variant="default" @click="detailsModal=false">Close</b-btn>
            </template>
        </shift-details-modal>
    </b-card>
</template>

<script>
    import FormatsNumbers from '../../mixins/FormatsNumbers';
    import ShiftDetailsModal from "../modals/ShiftDetailsModal";
    import FormatsDates from "../../mixins/FormatsDates";

    export default {
        components: {ShiftDetailsModal},

        mixins: [FormatsNumbers, FormatsDates],

        props: {},

        data() {
            return {
                sortBy: 'date',
                sortDesc: true,
                start_date: moment().startOf('isoweek').subtract(7, 'days').format('MM/DD/YYYY'),
                end_date: moment().format('MM/DD/YYYY'),
                processing: false,
                items: [],
                fields: [
                    {
                        key: 'date',
                        label: 'Deposit Date',
                        sortable: true,
                    },
                    {
                        key: 'count',
                        label: 'Deposit Count',
                        sortable: true,
                    },
                    {
                        key: 'sum',
                        label: 'Deposit Sum',
                        sortable: true,
                        formatter: this.numberFormat
                    },
                    'show_details'
                ],
                detailsModal: false,
                dateShiftsModal: false,
                dateShifts: [],
                selectedItem: {}
            }
        },

        mounted() {
        },

        computed: {
            totalItems() {
                return this.items.reduce((previous, current) => {
                    return previous + parseFloat(current.count);
                }, 0);
            },
            totalAmount() {
                return this.items.reduce((previous, current) => {
                    return previous + parseFloat(current.sum);
                }, 0);
            }
        },

        methods: {
            loadItems() {
                this.processing = true;
                axios.get('/admin/reports/bucket/?start_date=' + this.start_date + '&end_date=' + this.end_date)
                    .then(response => {
                        this.items = response.data;
                        this.processing = false;
                    });
            },
            toggleDate(shifts) {
                this.dateShiftsModal = !this.dateShiftsModal;
                this.dateShifts = shifts;
            },
            viewShift(shift) {
                this.detailsModal = true;
                this.selectedItem = shift;
            }
        }
    }
</script>

<style>
    table:not(.form-check) {
        font-size: 13px;
    }
</style>
