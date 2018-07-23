<template>
    <b-card :header="cardTitle"
            border-variant="info"
            header-bg-variant="info"
            header-text-variant="white">
        <!-- CLIENT DROPDOWNS -->

        <div v-show="isApproved" class="alert alert-success mb-3">This Timesheet has already been approved.</div>
        <div v-show="isDenied" class="alert alert-danger mb-3">This Timesheet has already been denied.</div>

        <b-row>
            <!-- CLIENT DROPDOWNS -->
            <b-col lg="6">
                <b-form-group label="Caregiver" label-for="caregiver_id">
                    <b-form-select
                            id="caregiver_id"
                            name="caregiver_id"
                            v-model="form.caregiver_id"
                            :disabled="isReviewing"
                    >
                        <option value="">-- Select Caregiver --</option>
                        <option v-for="item in caregivers" :value="item.id" :key="item.id">{{ item.name }}</option>
                    </b-form-select>
                    <input-help :form="form" field="caregiver_id" text=""></input-help>
                </b-form-group>
            </b-col>
            <b-col lg="6">
                <b-form-group label="Client" label-for="client_id">
                    <b-form-select
                            id="client_id"
                            name="client_id"
                            v-model="form.client_id"
                            :disabled="!hasClients || isReviewing"
                    >
                        <option value="">-- Select Client --</option>
                        <option v-for="item in caregiver.clients" :value="item.id" :key="item.id">{{ item.name }}</option>
                    </b-form-select>
                    <input-help :form="form" field="client_id" text=""></input-help>
                </b-form-group>
            </b-col>
            <!-- /end CLIENT DROPDOWNS -->
        </b-row>
        <!-- /end CLIENT DROPDOWNS -->
        
        <!-- WEEK DROPDOWN -->
        <b-row>
            <b-col lg="6">
                <b-form-group label="Week" label-for="week">
                    <b-form-select
                            id="week"
                            name="week"
                            v-model="week"
                            :disabled="isReviewing"
                    >
                        <option value="">-- Select Week --</option>
                        <option v-for="item in weekRanges" :value="item" :key="item.id">{{ item.display }}</option>
                    </b-form-select>
                    <input-help :form="form" field="week" text=""></input-help>
                </b-form-group>
            </b-col>
        </b-row>
        <!-- /end WEEK DROPDOWN -->
            
        <!-- SHIFTS TABLE -->
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th scope="col">Date</th>
                        <th scope="col">Time</th>
                        <th scope="col">Miles</th>
                        <th scope="col">Expenses</th>
                        <th scope="col">CG Rate</th>
                        <th scope="col">PV Rate</th>
                        <th scope="col">ADLs</th>
                        <th scope="col">Notes</th>
                        <th scope="col"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(day, index) in week.days" :key="index">
                        <td scope="row"> <!-- date -->
                            {{ formatDate(day) }}
                        </td>
                        <td scope="row"> <!-- time -->
                            {{ formatEntryDisplay(shifts[index]) }}
                        </td>
                        <td scope="row"> <!-- mileage -->
                            {{ shifts[index].mileage }}
                        </td>
                        <td scope="row"> <!-- other_expenses -->
                            ${{ shifts[index].other_expenses }}
                        </td>
                        <td scope="row"> <!-- caregiver_rate -->
                            ${{ shifts[index].caregiver_rate }}
                        </td>
                        <td scope="row"> <!-- provider_fee -->
                            ${{ shifts[index].provider_fee }}
                        </td>
                        <td scope="row"> <!-- activities -->
                            {{ shifts[index].activities.length }}
                        </td>
                        <td scope="row"> <!-- notes -->
                            {{ shifts[index].caregiver_comments ? 'Yes' : 'No' }}
                        </td>
                        <td scope="row" v-if="!isLocked"> <!-- action -->
                            <b-button variant="info" @click="editEntry(index)" :disabled="isBusy || ! canEdit">
                                <i class="fa fa-edit"></i>
                            </b-button>
                            <b-button variant="danger" @click="clearShift(index)" :disabled="isBusy || ! canEdit">
                                <i class="fa fa-trash-o"></i>
                            </b-button>
                        </td>
                        <td scope="row" v-else>
                            -
                        </td>
                    </tr>
                    <tr v-if="form.entries && form.entries.length == 0">
                        <td colspan="9" class="text-center">No timesheet entries</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <!-- /end SHIFTS TABLE -->

        <b-row class="mt-3" v-show="!isLocked">
            <b-col md="12">
                <b-button variant="info" type="button" @click="save(false)" :disabled="isBusy">
                    <i v-show="busy == 'save'" class="fa fa-spinner fa-spin"></i>
                    Save
                </b-button>
                <b-button variant="primary" type="button" @click="save(true)" :disabled="isBusy">
                    <i v-show="busy == 'approve'" class="fa fa-spinner fa-spin"></i>
                    Save and Create Shifts
                </b-button>
                <b-button v-if="! isCreating" variant="danger" type="button" @click="deny()" :disabled="isBusy">
                    <i v-show="busy == 'deny'" class="fa fa-spinner fa-spin"></i>
                    {{ isEditing ? 'Discard Timesheet' : 'Deny' }}
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
                <b-button variant="danger" @click="clearShift(deleteIndex, false)">Yes</b-button>
                <b-btn variant="default" @click="confirmDeleteModal = false">Cancel</b-btn>
            </div>
        </b-modal>
    </b-card>
</template>

<script>
    import ManageTimesheet from '../mixins/ManageTimesheet';
    import TimesheetEntryModal from './modals/TimesheetEntryModal';

    export default {
        mixins: [ ManageTimesheet ],
        components: { TimesheetEntryModal },

        data() {
            return {
                busy: false,
                deleteIndex: null,
                confirmDeleteModal: false,
            }
        },

        computed: {
            cardTitle() {
                if (this.isEditing) {
                    return 'Edit Manual Timesheet';
                } else if (this.isReviewing) {
                    return 'Review Manual Timesheet';
                } else if (this.isCreating) {
                    return 'Create Manual Timesheet';
                }
            },

            url() {
                let url = '/business/timesheet';
                if (this.form.id) { 
                    url = `${url}/${this.form.id}`;
                }
                return url;
            },

            isBusy() {
                return this.busy != false;
            }
        },

        methods: {
            save(approve = false) {
                this.busy = approve ? 'approve' : 'save';

                let url = this.url;
                if (approve) {
                    url += '?approve=1';
                }

                // submit only the entries filled out
                this.form.entries = this.form.entries.filter(x => x.checked_out_time != '');

                this.form.submit('post', url)
                    .then( ({ data }) => {
                        this.form = new Form(data.data);
                        this.loadTimesheet(data.data);
                        this.busy = false;
                    })
                    .catch(e => {
                        console.log('submit timesheet error:');
                        console.log(e);
                        // revert back to the unfiltered list
                        this.form.entries = this.shifts;
                        this.busy = false;
                    });
            },

            deny() {
                this.busy = 'deny';
                this.form.submit('post', this.url + '/deny')
                    .then( ({ data }) => {
                        this.form = new Form(data.data);
                        this.busy = false;
                    })
                    .catch(e => {
                        this.busy = fase;
                        console.log('submit timesheet error:');
                        console.log(e);
                    });
            },

            clearShift(index, confirm = true) {
                if (confirm) {
                    this.confirmDeleteModal = true;
                    this.deleteIndex = index;
                    return;
                }

                this.form.entries.splice(index, 1, {
                    ...this.emptyShift,
                    caregiver_rate: this.defaultRate || 0.00,
                    provider_fee: this.defaultFee || 0.00,
                });
                this.shifts = this.form.entries;
                this.deleteIndex = null;
                this.confirmDeleteModal = false;
            },

            loadTimesheet(timesheet) {
                this.sheet = timesheet;

                let entriesForDates = [];
                if (this.sheet.id) {
                    entriesForDates = this.sheet.entries.map(item => {
                        return {
                            date: moment.utc(item.checked_in_time).local().format('YYYY-MM-DD'),
                            entry: item,
                        }
                    });
                }
                this.shifts = this.generateEntriesForWeek(this.week, entriesForDates, this.defaultRate, this.defaultFee);
                this.form.entries = this.shifts;
            },
        },

        watch: {
            /**
            * sets client dropdown to only selected caregivers clients
            * and resets the shift form.
            */
            'form.caregiver_id': function(newVal, oldVal) {
                var results = this.caregivers.filter(function(c) {
                    return c.id == newVal;
                });

                if (results && results.length == 1) {
                    this.caregiver = results[0];
                    // only reset client_id if doesn't exist in dropdown
                    if (this.caregiver.clients.findIndex(item => item.id == this.form.client_id) == -1) {
                        this.form.client_id = '';
                    }
                } else {
                    this.caregiver = {};
                    this.form.client_id = '';
                }

            },

            /**
            * sets current selected client so rates/fees can be loaded
            * and resets the shift form.
            */
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

                this.loadTimesheet(this.sheet);
            },

            'week': function(newVal, oldVal) {
                this.loadTimesheet(this.sheet);
            },

            /**
            * Clear entry form when modal closes.
             */
            showEntryModal(val) {
                if (val == false) {
                    this.selectedEntry = {};
                }
            },
        },

    }
</script>
