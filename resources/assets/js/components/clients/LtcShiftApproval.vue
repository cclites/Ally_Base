<template>
    <div>
        <b-card :title="'Shift Approval (' + formatDate(startDate) + ' - ' + formatDate(endDate) + ')'">
            <b-row>
                <b-col cols="auto" class="mr-auto p-3">
                    <b-link @click="getWeek(weekOfYear - 1)" class="ml-1"><i class="fa fa-chevron-left"></i> Previous
                        Week
                    </b-link>
                </b-col>
                <b-col cols="auto" class="p-3">
                    <b-link v-if="!currentWeek" @click="getWeek(weekOfYear + 1)" class="mr-1">Next Week <i
                            class="fa fa-chevron-right"></i></b-link>
                </b-col>
            </b-row>
            <b-row v-if="items.length">
                <b-col>
                    <b-button v-b-modal.modal1>Sign and Approve These Shifts</b-button>
                </b-col>
            </b-row>
            <b-table show-empty :items="items"
                     :fields="fields">
                <template scope="data" slot="caregiver">
                    {{ data.item.caregiver.name }}
                </template>
            </b-table>
            <b-row v-if="items.length">
                <b-col>
                    <b-button v-b-modal.modal1>Sign and Approve These Shifts</b-button>
                </b-col>
            </b-row>
        </b-card>
        <!-- Modal Component -->
        <b-modal id="modal1"
                 :title="'Shift Approval (' + formatDate(startDate) + ' - ' + formatDate(endDate) + ')'"
                 :ok-disabled="!approved"
                 @ok="signShifts">
            <b-form-checkbox id="checkbox1"
                             v-model="approved"
                             :value="true"
                             :unchecked-value="false">
                I acknowledge that these shifts are correct.
            </b-form-checkbox>
            <div slot="modal-ok">
                Sign and Approve These Shifts
            </div>
        </b-modal>
    </div>
</template>

<style lang="scss">
</style>

<script>
    export default {
        props: ['shifts', 'weekStartDate', 'weekEndDate'],

        data() {
            return{
                approved: false,
                items: this.shifts,
                weekOfYear: moment(this.weekStartDate).week(),
                startDate: this.weekStartDate,
                endDate: this.weekEndDate,
                fields: [
                    {
                        key: 'caregiver',
                        label: 'Caregiver'
                    },
                    {
                        key: 'checked_in_time',
                        label: 'Start',
                        sortable: true
                    },
                    {
                        key: 'checked_out_time',
                        label: 'End',
                        sortable: true
                    },
                    {
                        key: 'hours_type',
                        label: 'Hours Type'
                    },
                    {
                        key: 'roundedShiftLength',
                        label: 'Shift Length'
                    },
                    {
                        key: 'mileage',
                        label: 'Mileage'
                    },
                    {
                        key: 'other_expenses',
                        label: 'Other Expenses'
                    }
                ]
            }
        },

        created() {

        },

        mounted() {

        },

        methods: {
            formatDate(date) {
                return moment(date).format('L');
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
            },

            signShifts() {
                let form = new Form({
                   week: this.weekOfYear
                });

                form.post('/shift-history/approve').then(response => {
                    this.approved = false;
                });
            }
        },

        computed: {

            currentWeek() {
                return moment().week() === moment(this.startDate).week();
            }
        }
    }
</script>