<template>
    <b-card :header="title"
            border-variant="info"
            header-bg-variant="info"
            header-text-variant="white">
            
        <!-- CLIENT DROPDOWNS -->
        <b-row v-if="isLocked">
            <b-col>
                <div class="alert alert-danger" v-if="isDenied"><strong>Denied.</strong> This timesheet has already been denied, it cannot be edited.</div>
                <div class="alert alert-success" v-if="isApproved"><strong>Approved.</strong> This timesheet has already been approved, it cannot be edited.</div>
            </b-col>
        </b-row>
        <b-row>
            <b-col lg="6">
                <b-form-group label="Client" label-for="client_id">
                    <b-form-select
                            :disabled="! hasClients"
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
                            :disabled="!!timesheet.id"
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
                <b-button variant="info" type="button" @click="submit()" :disabled="busy || isLocked">
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
                showEntryModal: false,
            }
        },

        computed: {
            title() {
                return this.timesheet.id ? 'View Timesheet' : 'Submit Timesheet';
            }
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

            async submit() {
                this.busy = true;
                let data = this.form.data();

                // submit only the entries filled out
                this.form.entries = this.form.entries.filter(x => x.checked_out_time != '');

                try {
                    if (this.timesheet.id) {
                        await this.form.put('/timesheets/' + this.timesheet.id);
                    }
                    else {
                        await this.form.post('/timesheets');
                    }
                }
                catch(e) {
                    console.log('submit timesheet error:');
                    console.log(e);
                    // revert back to the unfiltered list
                    this.form.entries = this.shifts;
                    this.busy = false;
                }
            },
        },

        mounted() {
            if (this.cg.id) {
                this.caregiver = this.cg;
                this.form.caregiver_id = this.cg.id;
            }
        },
    }
</script>
