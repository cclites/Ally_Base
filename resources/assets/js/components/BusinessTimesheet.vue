<template>
    <b-card header="Approve Timesheet"
            border-variant="info"
            header-bg-variant="info"
            header-text-variant="white">
        <!-- CLIENT DROPDOWNS -->
        <b-row class="mb-3">
            <b-col lg="6">
                <strong>Caregiver:</strong> {{ timesheet.caregiver.name }}
            </b-col>
            <b-col lg="6">
                <strong>Client:</strong> {{ timesheet.client.name }}
            </b-col>
        </b-row>
        <!-- /end CLIENT DROPDOWNS -->
            
        <!-- SHIFTS TABLE -->                
        <form @submit.prevent="addShift()" @keydown="shiftForm.clearError($event.target.name)">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th scope="col">Date</th>
                            <th scope="col">Start Time</th>
                            <th scope="col">End Time</th>
                            <th scope="col">Miles</th>
                            <th scope="col">Expenses</th>
                            <th scope="col">CG Rate</th>
                            <th scope="col">PV Rate</th>
                            <th scope="col">ALDS</th>
                            <th scope="col">Notes</th>
                            <th scope="col"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(item, index) in form.entries" :key="index">
                            <th scope="row"> <!-- date -->
                                {{ formatDate(item.checked_in_time) }}
                            </th>
                            <th scope="row"> <!-- start_time -->
                                {{ formatTime(item.checked_in_time) }}
                            </th>
                            <th scope="row"> <!-- end_time -->
                                {{ formatTime(item.checked_out_time) }}
                            </th>
                            <th scope="row"> <!-- mileage -->
                                {{ item.mileage }}
                            </th>
                            <th scope="row"> <!-- other_expenses -->
                                ${{ item.other_expenses }}
                            </th>
                            <th scope="row"> <!-- caregiver_rate -->
                                ${{ item.caregiver_rate }}
                            </th>
                            <th scope="row"> <!-- provider_fee -->
                                ${{ item.provider_fee }}
                            </th>
                            <th scope="row"> <!-- activities -->
                                {{ item.activities.length }}
                            </th>
                            <th scope="row"> <!-- notes -->
                                {{ item.caregiver_comments ? 'Yes' : 'No' }}
                            </th>
                            <th scope="row"> <!-- action -->
                                <b-button variant="success">
                                    <i class="fa fa-edit" @click="editEntry(item)"></i>
                                </b-button>
                                <b-button variant="danger" @click="removeShift(index)">
                                    <i class="fa fa-trash-o"></i>
                                </b-button>
                            </th>
                        </tr>
                    </tbody>
                </table>
            </div>
        </form>
        <!-- /end SHIFTS TABLE -->

        <!-- ACTIVITIES / NOTES -->
        <!-- <b-row class="mt-3">
            <b-col md="6">
                <label>Global Activities</label>
                <div class="form-check">
                    <label class="custom-control custom-checkbox" v-for="activity in activities" :key="activity.id" style="clear: left; float: left;">
                        <input type="checkbox" class="custom-control-input" v-model="form.activities" :value="activity.id">
                        <span class="custom-control-indicator"></span>
                        <span class="custom-control-description">{{ activity.code }} - {{ activity.name }}</span>
                    </label>
                </div>
            </b-col>
            <b-col md="6">
                <b-form-group label="Global Notes" label-for="caregiver_comments">
                    <b-textarea
                            id="caregiver_comments"
                            name="caregiver_comments"
                            :rows="8"
                            v-model="form.caregiver_comments"
                    ></b-textarea>
                    <input-help :form="form" field="caregiver_comments" text=""></input-help>
                </b-form-group>
            </b-col>
        </b-row> -->
        <!-- /end ACTIVITIES / NOTES -->

        <b-row class="mt-3">
            <b-col md="12">
                <b-button variant="success" type="button" @click="submit()">Save and Approve Timesheet</b-button>
            </b-col>
        </b-row>

        <timesheet-entry-modal
            :entry="selectedEntry"
            :activities="activities"
            v-model="showEntryModal"
        ></timesheet-entry-modal>

        <b-modal title="Delete Timesheet Entry" v-model="confirmDeleteModal">
            Are you sure you want to remove this timesheet entry?
            <div slot="modal-footer">
                <b-button variant="danger" @click="removeShift(deleteIndex, false)">Yes</b-button>
                <b-btn variant="default" @click="confirmDeleteModal = false">Cancel</b-btn>
            </div>
        </b-modal>
    </b-card>
</template>

<script>
    import FormatDates from '../mixins/FormatsDates';
    import TimesheetEntryModal from './modals/TimesheetEntryModal';

    export default {
        mixins: [FormatDates],
        components: {TimesheetEntryModal},

        props: {
            'activities': {
                type: Array,
                default: [],
            },
            'timesheet': {
                type: Object,
                default: {},
            }
        },

        data() {
            return{
                caregiver: {},
                client: {},
                form: new Form({}),
                shiftForm: new Form(this.newShift()),
                showEntryModal: false,
                selectedEntry: {},
                deleteIndex: null,
                confirmDeleteModal: false,
            }
        },

        mounted() {
            // if (this.isCaregiver) {
            //     this.form.caregiver_id = this.caregiver.id;
            // }
            this.form = new Form(this.timesheet);
        },

        methods: {
            editEntry(entry) {
                this.selectedEntry = entry;
                this.showEntryModal = true;
            },

            newShift() {
                console.log("default rate;");
                console.log(this.defaultRate);
                return {
                    date: '',
                    start_time: '',
                    end_time: '',
                    mileage: 0,
                    other_expenses: 0,
                    caregiver_rate: this.defaultRate || 0,
                    provider_fee: this.defaultFee || 0,
                }
            },

            submit() {
                this.form.submit('post', '/business/manual-timesheets')
                    .then( ({ data }) => {
                        console.log(data);
                    })
                    .catch(e => {
                        console.log('submit timesheet error:');
                        console.log(e);
                    });
            },

            addShift() {
                this.shiftForm.clearError();
                if (!this.isValidShift(this.shiftForm)) {
                    return;
                }

                this.form.entries.push(this.shiftForm.data())

                this.shiftForm = new Form(this.newShift());
            },

            removeShift(index, confirm = true) {
                if (confirm) {
                    this.confirmDeleteModal = true;
                    this.deleteIndex = index;
                    return;
                }

                this.form.entries.splice(index, 1);
                this.deleteIndex = null;
                this.confirmDeleteModal = false;
            },

            isValidShift(data) {
                if (!this.validDate(data.date)) {
                    this.shiftForm.addError('date', 'Required');
                }
                
                if (!this.validDate(data.start_time)) {
                    this.shiftForm.addError('start_time', 'Required');
                }
                
                if (!this.validDate(data.end_time)) {
                    this.shiftForm.addError('end_time', 'Required');
                }

                if (data.mileage === '' || isNaN(data.mileage)) {
                    this.shiftForm.addError('mileage', 'Invalid');
                }
                
                if (isNaN(data.other_expenses)) {
                    this.shiftForm.addError('other_expenses', 'Invalid');
                }

                if (isNaN(data.caregiver_rate)) {
                    this.shiftForm.addError('caregiver_rate', 'Invalid');
                }

                if (isNaN(data.provider_fee)) {
                    this.shiftForm.addError('provider_fee', 'Invalid');
                }
                
                return !this.shiftForm.hasError();
            },

            validDate(val) {
                if (!val || val == '') return false;
                return moment(val, 'mm/dd/yyyy').isValid();
            },
            
            validTime() {
                if (!val || val == '') return false;
                return moment(this.value, 'hh:mm').isValid();
            }
        },

        computed: {
            isCaregiver() {
                return this.caregiver.id ? true : false;
            },

            hasClients() {
                return this.caregiver.clients && this.caregiver.clients.length > 0;
            },

            defaultRate() {
                return this.client.caregiver_hourly_rate || 0;
            },

            defaultFee() {
                return this.client.provider_hourly_fee || 0;
            },
        },

        watch: {
            // sets client dropdown to only selected caregivers clients
            // 'form.caregiver_id': function(newVal, oldVal) {
            //     var results = this.caregivers.filter(function(c) {
            //         return c.id == newVal;
            //     });

            //     if (results && results.length == 1) {
            //         this.caregiver = results[0];
            //     } else {
            //         this.caregiver = {};
            //     }

            //     this.form.client_id = '';
            // },

            // sets current selected client so rates/fees can be loaded
            // and resets the shift form
            'form.client_id': function(newVal, oldVal) {
                if (this.caregiver.clients) {
                    var results = this.caregiver.clients.filter(function(c) {
                        return c.id == newVal;
                    });

                    if (results && results.length == 1) {
                        this.client = results[0];
                    } else {
                        this.client = {};
                    }
                } else {
                    this.client = {};
                }
                
                this.shiftForm = new Form(this.newShift());
            },
        }
    }
</script>