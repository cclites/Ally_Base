<template>
    <b-card header="Review Manual Timesheet"
            border-variant="info"
            header-bg-variant="info"
            header-text-variant="white">
        <!-- CLIENT DROPDOWNS -->

        <div v-show="isApproved" class="alert alert-success mb-3">This Timesheet has already been approved.</div>
        <div v-show="isDenied" class="alert alert-danger mb-3">This Timesheet has already been denied.</div>

        <b-row class="mb-3">
            <!-- CLIENT DROPDOWNS -->
            <b-col lg="6">
                <b-form-group label="Caregiver" label-for="caregiver_id">
                    <b-form-select
                            id="caregiver_id"
                            name="caregiver_id"
                            v-model="form.caregiver_id"
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
                            :disabled="!hasClients"
                            id="client_id"
                            name="client_id"
                            v-model="form.client_id"
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
        
        <!-- SHIFTS TABLE -->
        <b-btn variant="info" @click="createEntry()">
            <i class="fa fa-plus"></i> Add Entry
        </b-btn>
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
                        <td scope="row"> <!-- date -->
                            {{ formatDateFromUTC(item.checked_in_time) }}
                        </td>
                        <td scope="row"> <!-- start_time -->
                            {{ formatTimeFromUTC(item.checked_in_time) }}
                        </td>
                        <td scope="row"> <!-- end_time -->
                            {{ formatTimeFromUTC(item.checked_out_time) }}
                        </td>
                        <td scope="row"> <!-- mileage -->
                            {{ item.mileage }}
                        </td>
                        <td scope="row"> <!-- other_expenses -->
                            ${{ item.other_expenses }}
                        </td>
                        <td scope="row"> <!-- caregiver_rate -->
                            ${{ item.caregiver_rate }}
                        </td>
                        <td scope="row"> <!-- provider_fee -->
                            ${{ item.provider_fee }}
                        </td>
                        <td scope="row"> <!-- activities -->
                            {{ item.activities.length }}
                        </td>
                        <td scope="row"> <!-- notes -->
                            {{ item.caregiver_comments ? 'Yes' : 'No' }}
                        </td>
                        <td scope="row" v-if="!isDenied && !isApproved"> <!-- action -->
                            <b-button variant="info" @click="editEntry(item, index)" :disabled="busy > 0">
                                <i class="fa fa-edit"></i>
                            </b-button>
                            <b-button variant="danger" @click="removeShift(index)" :disabled="busy > 0">
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

        <b-row class="mt-3" v-show="!isDenied && !isApproved">
            <b-col md="12">
                <b-button variant="info" type="button" @click="save(false)" :disabled="busy > 0">
                    <i v-show="busy == 1" class="fa fa-spinner fa-spin"></i>
                    Save
                </b-button>
                <b-button variant="primary" type="button" @click="save(true)" :disabled="busy > 0">
                    <i v-show="busy == 1" class="fa fa-spinner fa-spin"></i>
                    Save and Create Shifts
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
            'activities': { type: Array, default: [] },
            'timesheet': { type: Object, default: {} },
            'caregivers': { type: Array, default: [] },
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
            if (this.timesheet.id) {
                this.form = new Form(this.timesheet);
            } else {
                this.form = new Form({
                    approved_at: null,
                    business: {},
                    business_id: '',
                    caregiver: {},
                    caregiver_id: '',
                    client: {},
                    client_id: '',
                    created_at: '',
                    creator_id: '',
                    denied_at: null,
                    entries: [],
                    id: '',
                    updated_at: '',
                });
            }
        },

        methods: {
            createEntry() {
                this.selectedEntry = this.emptyShift();
                this.selectedIndex = -1;
                this.showEntryModal = true;
            },

            updateEntry(entry) {
                if (this.selectedIndex > -1) {
                    this.form.entries.splice(this.selectedIndex, 1, entry);
                } else {
                    this.form.entries.push(entry);
                }
                this.selectedEntry = null;
            },

            editEntry(entry, index) {
                this.selectedEntry = entry;
                this.selectedIndex = index;
                this.showEntryModal = true;
            },

            emptyShift() {
                return {
                    id: '',
                    caregiver_comments: '',
                    checked_in_time: moment.utc(),
                    checked_out_time: '',
                    mileage: 0,
                    other_expenses: 0,
                    caregiver_rate: this.defaultRate || 0,
                    provider_fee: this.defaultFee || 0,
                    activities: [],
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

                // this.shiftForm = new Form(this.emptyShift());
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
                return this.form.id && this.form.approved_at;
            },

            isDenied() {
                return this.form.id && this.form.denied_at;
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
            },
        }
    }
</script>