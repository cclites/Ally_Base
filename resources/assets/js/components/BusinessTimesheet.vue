<template>
    <b-card header="Review Manual Timesheet"
            border-variant="info"
            header-bg-variant="info"
            header-text-variant="white">
        <!-- CLIENT DROPDOWNS -->

        <div v-show="isApproved" class="alert alert-success mb-3">This Timesheet has already been approved.</div>
        <div v-show="isDenied" class="alert alert-danger mb-3">This Timesheet has already been denied.</div>

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
                            {{ formatDateFromUTC(item.checked_in_time) }}
                        </th>
                        <th scope="row"> <!-- start_time -->
                            {{ formatTimeFromUTC(item.checked_in_time) }}
                        </th>
                        <th scope="row"> <!-- end_time -->
                            {{ formatTimeFromUTC(item.checked_out_time) }}
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
                        <th scope="row" v-if="!isDenied && !isApproved"> <!-- action -->
                            <b-button variant="success" @click="editEntry(item, index)" :disabled="busy > 0">
                                <i class="fa fa-edit"></i>
                            </b-button>
                            <b-button variant="danger" @click="removeShift(index)" :disabled="busy > 0">
                                <i class="fa fa-trash-o"></i>
                            </b-button>
                        </th>
                        <th scope="row" v-else>
                            -
                        </th>
                    </tr>
                </tbody>
            </table>
        </div>
        <!-- /end SHIFTS TABLE -->

        <b-row class="mt-3" v-show="!isDenied && !isApproved">
            <b-col md="12">
                <b-button variant="success" type="button" @click="save(true)" :disabled="busy > 0">
                    <i v-show="busy == 1" class="fa fa-spinner fa-spin"></i>
                    Save and Approve
                </b-button>
                <b-button variant="danger" type="button" @click="deny()" :disabled="busy > 0">
                    <i v-show="busy == 2" class="fa fa-spinner fa-spin"></i>
                    Deny
                </b-button>
            </b-col>
        </b-row>

        <timesheet-entry-modal
            :entry="selectedEntry"
            :activities="activities"
            v-model="showEntryModal"
            @updated="updateEntry"
            :isOfficeUser="true"
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
        mixins: [ FormatDates ],
        components: { TimesheetEntryModal },

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
            return {
                busy: 0,
                caregiver: {},
                client: {},
                form: new Form({}),
                showEntryModal: false,
                selectedEntry: {},
                selectedIndex: null,
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
            deny() {

            },
            
            updateEntry(entry) {
                this.form.entries.splice(this.selectedIndex, 1, entry);
                this.selectedEntry = null;
            },

            editEntry(entry, index) {
                this.selectedEntry = entry;
                this.selectedIndex = index;
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

            save(approve = false) {
                this.busy = 1;
                this.form.submit('post', this.url + approve ? '?approve=1' : '')
                    .then( ({ data }) => {
                        this.form = new Form(data.data);
                        this.busy = 0;
                    })
                    .catch(e => {
                        this.busy = 0;
                        console.log('submit timesheet error:');
                        console.log(e);
                    });
            },

            deny() {
                this.busy = 2;
                this.form.submit('post', this.url + '?deny=1')
                    .then( ({ data }) => {
                        this.form = new Form(data.data);
                        this.busy = 0;
                    })
                    .catch(e => {
                        this.busy = 0;
                        console.log('submit timesheet error:');
                        console.log(e);
                    });
            },

            addShift() {
                // this.shiftForm.clearError();
                // if (!this.isValidShift(this.shiftForm)) {
                //     return;
                // }

                // this.form.entries.push(this.shiftForm.data())

                // this.shiftForm = new Form(this.newShift());
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
        },

        computed: {
            url() {
                let url = '/business/timesheet';
                if (this.form.id) { 
                    url = `${url}/${this.form.id}`;
                }
                return url;
            },

            isApproved() {
                return this.form.approved_at;
            },

            isDenied() {
                return this.form.denied_at;
            },

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
            // 'form.client_id': function(newVal, oldVal) {
            //     if (this.caregiver.clients) {
            //         var results = this.caregiver.clients.filter(function(c) {
            //             return c.id == newVal;
            //         });

            //         if (results && results.length == 1) {
            //             this.client = results[0];
            //         } else {
            //             this.client = {};
            //         }
            //     } else {
            //         this.client = {};
            //     }
                
            //     this.shiftForm = new Form(this.newShift());
            // },
        }
    }
</script>