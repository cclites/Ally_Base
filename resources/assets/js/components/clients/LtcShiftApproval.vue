<template>
    <div>
        <b-card :title="'Shift Approval (' + formatDate(startDate) + ' - ' + formatDate(endDate) + ')'">
            <b-row>
                <b-col cols="auto" class="mr-auto p-3">
                    <b-link @click="getWeek(weekOfYear - 1)" class="ml-1">
                        <i class="fa fa-chevron-left"></i>
                        Previous Week
                    </b-link>
                </b-col>
                <b-col cols="auto" class="p-3">
                    <b-link v-if="!currentWeek" @click="getWeek(weekOfYear + 1)" class="mr-1">
                        Next Week
                        <i class="fa fa-chevron-right"></i>
                    </b-link>
                </b-col>
            </b-row>
            <b-row v-if="items.length">
                <b-col>
                    <b-button v-b-modal.modal1 v-if="!shiftsVerified">Sign and Approve These Shifts</b-button>
                    <div v-else class="ml-2"><em>Shifts for this week have been approved.</em></div>
                </b-col>
            </b-row>
            <div class="table-responsive">
                <b-table show-empty :items="items"
                         :fields="fields">
                    <template scope="data" slot="caregiver">
                        {{ data.item.caregiver.name }}
                    </template>
                    <template scope="data" slot="actions">
                        <b-button @click="shiftDetails(data.item)">View</b-button>
                    </template>
                </b-table>
            </div>
            <b-row v-if="items.length">
                <b-col>
                    <b-button v-b-modal.modal1 v-if="!shiftsVerified">Sign and Approve These Shifts</b-button>
                    <div v-else class="ml-2"><em>Shifts for this week have been approved.</em></div>
                </b-col>
            </b-row>
        </b-card>
        <!-- Modal Component -->
        <b-modal id="modal1"
                 :title="'Shift Approval (' + formatDate(startDate) + ' - ' + formatDate(endDate) + ')'"
                 :ok-disabled="!form.approved || form.name.length < 2"
                 @ok="signShifts">
            <b-form-group label="Enter Your Full Name" label-for="name">
                <b-form-input type="text"
                              id="name"
                              v-model="form.name"
                />
                <input-help :form="form" field="name" text="" />
            </b-form-group>
            <b-form-checkbox id="checkbox1"
                             v-model="form.approved"
                             :value="true"
                             :unchecked-value="false">
                I acknowledge that these shifts are correct.
                <input-help :form="form" field="approved" text="" />
            </b-form-checkbox>
            <div slot="modal-ok">
                Sign and Approve These Shifts
            </div>
        </b-modal>

        <b-modal id="modal_details"
                 ref="modalDetails"
                 :title="'Shift ' + formatDate(startDate)"
                 size="lg">
            <b-container fluid>
                <b-row class="with-padding-bottom">
                    <b-col sm="6">
                        <strong>Caregiver:</strong>
                        <br>
                        {{ currentItem.caregiver.name }}
                    </b-col>
                    <b-col sm="6">
                        <strong>Check In Time:</strong>
                        <br>
                        {{ formatDateTime(currentItem.checked_in_time) }}
                    </b-col>
                </b-row>
                <b-row class="with-padding-bottom">
                    <b-col sm="6">
                        <strong>Check Out Time:</strong>
                        <br>
                        {{ formatDateTime(currentItem.checked_out_time) }}
                    </b-col>

                    <b-col sm="6">
                        <strong>Shift Length:</strong>
                        <br>
                        {{ currentItem.duration }}hrs
                    </b-col>
                </b-row>
                <b-row class="with-padding-bottom">
                    <b-col sm="6">
                        <strong>Hours Type:</strong>
                        <br>
                        {{ currentItem.hours_type }}
                    </b-col>
                    <b-col sm="6">
                        <strong>Mileage:</strong>
                        <br>
                        {{ currentItem.mileage }}
                    </b-col>
                </b-row>
                <b-row class="with-padding-bottom">
                    <b-col sm="6">
                        <strong>Other Expenses:</strong>
                        <br>
                        {{ formatMoney(currentItem.other_expenses) }}
                    </b-col>
                    <b-col sm="6">
                        <strong>Caregiver Rate:</strong>
                        <br>
                        {{ formatMoney(currentItem.caregiver_rate) }}
                    </b-col>
                </b-row>
                <b-row class="with-padding-bottom">
                    <b-col sm="6">
                        <strong>Provider Fee:</strong>
                        <br>
                        {{ formatMoney(currentItem.provider_fee) }}
                    </b-col>
                    <b-col sm="6">
                        <strong>Status:</strong>
                        <br>
                        {{ currentItem.status }}
                    </b-col>
                </b-row>
                <b-row class="with-padding-bottom">
                    <b-col>
                        <strong>Caregiver Comments:</strong>
                        <br>
                        {{ currentItem.caregiver_comments }}
                    </b-col>
                </b-row>
            </b-container>
        </b-modal>
    </div>
</template>

<script>
    import FormatsDates from '../../mixins/FormatsDates';

    export default {
        props: ['shifts', 'weekStartDate', 'weekEndDate', 'verified'],

        mixins: [FormatsDates],

        data() {
            return {
                shiftsVerified: this.verified,
                currentItem: {
                    caregiver: {}
                },
                form: new Form({
                  approved: false,
                  name: "",
                  week: null,
                }),
                items: this.shifts,
                weekOfYear: moment(this.weekStartDate).week(),
                startDate: this.weekStartDate,
                endDate: this.weekEndDate,
                fields: [
                    {
                        key: 'checked_in_time',
                        label: 'Start',
                        sortable: true,
                        formatter: (value) => { return this.formatDateTime(value) }
                    },
                    {
                        key: 'checked_out_time',
                        label: 'End',
                        sortable: true,
                        formatter: (value) => { return this.formatDateTime(value) }
                    },
                    {
                        key: 'caregiver',
                        label: 'Caregiver'
                    },
                    {
                        key: 'duration',
                        label: 'Shift Length'
                    },
                    {
                        key: 'total',
                        label: 'Total Amount',
                        formatter: (value) => { return this.formatMoney(value); }

                    },
                    {
                        key: 'actions',
                        class: 'hidden-print'
                    }
                ]
            }
        },

        methods: {

            formatMoney(value) {
                return numeral(value).format('$0,0.00');
            },

            getWeek(week) {
                axios.get('/shift-history/' + week)
                    .then(response => {
                        this.refreshData(response);
                    }).catch(error => {
                    console.error(error.response);
                });
            },

            refreshData(response) {
                this.items = response.data.shifts;
                this.startDate = response.data.week_start_date;
                this.endDate = response.data.week_end_date;
                this.weekOfYear = moment(this.startDate).week();
                this.shiftsVerified = response.data.shifts_verified;
            },

            async signShifts() {
                this.form.week = this.weekOfYear;

                try {
                  await this.form.post('/shift-history/approve');
                  this.getWeek(this.weekOfYear);
                  this.form.approved = false;
                  return true;
                }
                catch (err) {
                  return false;
                }
            },

            shiftDetails(item) {
                this.currentItem = item;
                this.$refs.modalDetails.show();
            }
        },

        computed: {
            currentWeek() {
                return moment().week() === moment(this.startDate).week();
            }
        }
    }
</script>
