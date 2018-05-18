<template>
    <b-card header="Submit Timesheet"
            border-variant="info"
            header-bg-variant="info"
            header-text-variant="white">
            
        <!-- CLIENT DROPDOWNS -->
        <b-row>
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
                    >
                        <option value="">-- Select Week --</option>
                        <option v-for="item in weekRanges" :value="item" :key="item.id">{{ item.display }}</option>
                    </b-form-select>
                    <input-help :form="form" field="week" text=""></input-help>
                </b-form-group>
            </b-col>
        </b-row>
        <!-- /end WEEK DROPDOWN -->
            
        <!-- ENTRIES TABLE -->
        <div class="table-responsive">
            <input-help :form="form" field="entries" text=""></input-help>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th scope="col"></th>
                        <th scope="col"></th>
                        <th scope="col"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(day, index) in week.days" :key="index">
                        <th>{{ dow(day) }}</th>
                        <td>
                            {{ formatEntryDisplay(shifts[index]) }}
                        </td>
                        <td>
                            <b-button variant="info" size="xs" @click="editEntry(index)" :disabled="! canEdit">
                                <i class="fa fa-edit"></i>
                            </b-button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <!-- /end ENTRIES TABLE -->

        <b-row class="mt-3">
            <b-col md="12">
                <b-button variant="info" type="button" @click="submit()" :disabled="busy">
                    <i v-show="busy" class="fa fa-spinner fa-spin"></i>
                    Submit Timesheet
                </b-button>
            </b-col>
        </b-row>

        <timesheet-entry-modal
            :entry="selectedEntry"
            :activities="activities"
            v-model="showEntryModal"
            @updated="updateEntry"
        ></timesheet-entry-modal>

    </b-card>
</template>

<script>
    import TimesheetEntryModal from '../modals/TimesheetEntryModal';
    import ManageTimesheet from '../../mixins/ManageTimesheet';

    export default {
        mixins: [ ManageTimesheet ],
        components: { TimesheetEntryModal },

        props: {
            'cg': { type: Object, default: {} },
        },

        data() {
            return {
                busy: false,
                caregiver: {},
                client: {},
                weekRanges: [],
                week: {},
                shifts: [],
                form: new Form({}),
                showEntryModal: false,
                selectedEntry: {},
                selectedIndex: null,
            }
        },

        computed: {
            hasClients() {
                return this.caregiver.clients && this.caregiver.clients.length > 0;
            },

            defaultRate() {
                return this.client.caregiver_hourly_rate || 0;
            },

            defaultFee() {
                return this.client.provider_hourly_fee || 0;
            },

            canEdit() {
                return this.form.client_id ? true : false;
            },
        },

        methods: {
            editEntry(index) {
                this.selectedIndex = index;
                this.selectedEntry = this.form.entries[index];

                // set default check in time for day of the week
                if (! this.selectedEntry.checked_in_time) {
                    this.selectedEntry.checked_in_time = moment(this.week.days[index], 'YYYY-MM-DD');
                }

                this.showEntryModal = true;
            },

            updateEntry(entry) {
                this.form.entries.splice(this.selectedIndex, 1, entry);
                this.shifts.splice(this.selectedIndex, 1, entry);
                this.selectedEntry = null;
            },

            submit() {
                this.busy = true;
                let data = this.form.data();

                // submit only the entries filled out
                this.form.entries = this.form.entries.filter(x => x.checked_out_time != '');

                this.form.submit('post', '/timesheet')
                    .then( ({ data }) => {
                        window.location = '/timesheet?success=1';
                        // this.form.client_id = '';
                        // this.form.shifts = [];
                        // this.week = this.weekRanges[0];
                    })
                    .catch(e => {
                        console.log('submit timesheet error:');
                        console.log(e);
                        // revert back to the unfiltered list
                        this.form.entries = this.shifts;
                        this.busy = false;
                    });
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
                } else {
                    this.caregiver = {};
                }

                this.form.client_id = '';
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
                
                this.shifts = this.form.entries = this.generateEntriesForWeek(this.week, [], this.defaultRate, this.defaultFee);
            },

            'week': function(newVal, oldVal) {
                this.shifts = this.form.entries = this.generateEntriesForWeek(this.week, [],this.defaultRate, this.defaultFee);
            }
        },

        mounted() {
            if (this.cg.id) {
                this.caregiver = this.cg;
                this.form.caregiver_id = this.caregiver.id;
            }

            this.weekRanges = this.generateWeeks();
            this.week = this.weekRanges[0];
            this.form = new Form(this.emptyTimesheet);
        },
    }
</script>