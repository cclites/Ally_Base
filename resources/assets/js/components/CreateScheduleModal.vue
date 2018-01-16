<template>
    <div>
        <b-modal id="createScheduleModal"
                 title="Schedule Shift"
                 class="modal-fit-more"
                 size="lg"
                 :no-close-on-backdrop="true"
                 v-model="createModel"
                 v-if="!maxHoursWarning"
        >
            <b-card no-body>
                <b-tabs card>
                    <b-tab title="Initial Shift" active>
                        <b-row>
                            <b-col sm="6">
                                <b-form-group label="Client" label-for="client_id">
                                    <b-form-select
                                            id="client_id"
                                            name="client_id"
                                            v-model="form.client_id"
                                            required
                                    >
                                        <option value="">--Select a Client--</option>
                                        <option v-for="item in clients" :value="item.id">{{ item.name }}</option>
                                    </b-form-select>
                                    <input-help :form="form" field="client_id" text="Select the client for this schedule." />
                                </b-form-group>
                            </b-col>
                            <b-col sm="6">
                                <b-form-group label="Assigned Caregiver" label-for="caregiver_id">
                                    <b-form-select
                                            id="caregiver_id"
                                            name="caregiver_id"
                                            v-model="form.caregiver_id"
                                    >
                                        <option value="">--Not Assigned--</option>
                                        <option v-for="caregiver in caregivers" :value="caregiver.id">{{ caregiver.name }}</option>
                                    </b-form-select>
                                    <input-help :form="form" field="caregiver_id" text="Select the caregiver for this schedule." />
                                </b-form-group>
                            </b-col>
                        </b-row>
                        <b-row>
                            <b-col sm="6">
                                <b-form-group label="Caregiver Rate" label-for="caregiver_rate">
                                    <b-form-input
                                            id="caregiver_rate"
                                            name="caregiver_rate"
                                            type="number"
                                            step="any"
                                            v-model="form.caregiver_rate"
                                    >
                                    </b-form-input>
                                    <input-help :form="form" field="caregiver_rate" text="Enter the hourly rate paid to the caregiver." />
                                </b-form-group>
                            </b-col>
                            <b-col sm="6">
                                <b-form-group label="Provider Fee" label-for="provider_fee">
                                    <b-form-input
                                            id="provider_fee"
                                            name="provider_fee"
                                            type="number"
                                            step="any"
                                            v-model="form.provider_fee"
                                    >
                                    </b-form-input>
                                    <input-help :form="form" field="provider_fee" text="Enter the hourly fee charged by the provider." />
                                </b-form-group>
                            </b-col>
                            <b-col sm="12">
                                Payment Type: {{ paymentType }} ({{ displayAllyPct }}% Processing Fee)
                            </b-col>
                            <b-col sm="6">
                                <b-form-group label="Ally Fee" label-for="ally_fee">
                                    {{ allyFee }}
                                </b-form-group>
                            </b-col>
                            <b-col sm="6">
                                <b-form-group label="Total Rate" label-for="ally_fee">
                                    {{ totalRate }}
                                </b-form-group>
                            </b-col>
                        </b-row>
                        <b-row>
                            <b-col lg="12">
                                <b-form-group label="Start Date" label-for="startDate">
                                    <date-picker v-model="startDate" />
                                    <input-help :form="form" field="starts_at" text="Confirm the starting date." />
                                </b-form-group>
                            </b-col>
                        </b-row>
                        <b-row>
                            <b-col sm="6">
                                <b-form-group label="Start Time" label-for="startTime">
                                    <time-picker
                                            id="startTime"
                                            name="startTime"
                                            v-model="startTime"
                                    />
                                    <input-help :form="form" field="starts_at" text="Confirm the starting time." />
                                </b-form-group>
                            </b-col>
                            <b-col sm="6">
                                <b-form-group label="End Time" label-for="endTime">
                                    <time-picker
                                            id="endTime"
                                            name="endTime"
                                            v-model="endTime"
                                    />
                                    <input-help :form="form" field="duration" text="Confirm the ending time." />
                                </b-form-group>
                            </b-col>
                        </b-row>
                        <b-row>
                            <b-col lg="12">
                                <b-form-group label="Special Shift Designation" label-for="hours_type">
                                    <b-form-select
                                            id="hours_type"
                                            name="hours_type"
                                            v-model="form.hours_type"
                                    >
                                        <option value="default">Regular Shift</option>
                                        <option value="holiday">Holiday</option>
                                        <option value="overtime">Overtime</option>
                                    </b-form-select>
                                    <input-help :form="form" field="hours_type" text="" />
                                    <small class="form-text text-info" v-if="specialHoursChange">
                                        Be sure to update the caregiver's rates to reflect this special designation.
                                    </small>
                                </b-form-group>
                            </b-col>
                        </b-row>
                    </b-tab>
                    <b-tab title="Recurrence">
                        <b-row>
                            <b-col lg="12">
                                <b-form-group label="Recurring Period" label-for="interval_type">
                                    <b-form-select
                                            id="interval_type"
                                            name="interval_type"
                                            v-model="form.interval_type"
                                    >
                                        <option value="">Single Shift Only</option>
                                        <option value="weekly">Weekly</option>
                                        <option value="biweekly">Bi-weekly</option>
                                        <option value="monthly">Monthly</option>
                                    </b-form-select>
                                    <input-help :form="form" field="interval_type" text="Select how often the schedule repeats." />
                                </b-form-group>
                                <div v-if="form.interval_type">
                                    <div class="form-check" v-show="form.interval_type === 'weekly' || form.interval_type === 'biweekly'">
                                        <input-help :form="form" field="bydays" text="Select the days of the week below." />
                                        <label class="custom-control custom-checkbox" v-for="(item, index) in daysOfWeek">
                                            <input type="checkbox" class="custom-control-input" name="bydays[]" v-model="form.bydays" :value="item">
                                            <span class="custom-control-indicator"></span>
                                            <span class="custom-control-description">{{ index }}</span>
                                        </label>
                                    </div>
                                    <p v-show="form.interval_type === 'monthly'">
                                        The schedule will repeat every month on the {{ dayOfMonth(form.start_date) }}.
                                    </p>
                                    <b-form-group label="End date" label-for="end_date">
                                        <date-picker v-model="form.recurring_end_date" />
                                        <input-help :form="form" field="end_date" text="Repeat the schedule until this date." />
                                    </b-form-group>
                                </div>
                            </b-col>
                        </b-row>
                    </b-tab>
                    <b-tab title="Notes">
                        <b-row>
                            <b-col lg="12">
                                <b-form-group label="Schedule Notes" label-for="notes">
                                    <b-form-textarea
                                            id="notes"
                                            name="notes"
                                            :rows="6"
                                            v-model="form.notes"
                                    >
                                    </b-form-textarea>
                                    <input-help :form="form" field="notes" text="Enter any notes relating to this scheduled shift." />
                                </b-form-group>
                            </b-col>
                        </b-row>
                    </b-tab>
                </b-tabs>
            </b-card>

            <div slot="modal-footer">
                <b-btn variant="default" @click="createModel=false">Close</b-btn>
                <b-btn variant="info" @click="submitForm()">Save</b-btn>
            </div>
        </b-modal>
        <b-modal id="maxHoursWarning" title="Schedule Shift" v-model="createModel" v-else-if="maxHoursWarning">
            <b-container fluid>
                <h4>This will put the client over the maximum weekly hours.  Are you sure you want to do this?</h4>
            </b-container>
            <div slot="modal-footer">
                <b-btn variant="default" @click="createModel=false">No, Cancel</b-btn>
                <b-btn variant="danger" @click="submitForm()">Yes, Save</b-btn>
            </div>
        </b-modal>
    </div>
</template>

<script>
    import ScheduleForm from '../mixins/ScheduleForm';
    import DatePicker from "./DatePicker";

    export default {
        components: {DatePicker},

        mixins: [ScheduleForm],

        props: {
            client: {
                type: Object,
                default() {
                    return {};
                }
            },
            model: Boolean,
            defaultValues: {
                type: Object,
                default() {
                    return {};
                }
            },
            selectedEvent: {
                type: Object,
                default() {
                    return moment();
                }
            }
        },
        
        data() {
            return {
                startDate: "",
                startTime: "",
                endTime: "",
                endDate: "",
                createModel: this.model,
                form: new Form(),
            }
        },

        mounted() {
            this.loadClientData();
        },

        computed: {},

        methods: {
            makeForm() {
                console.log('makeForm init');
                this.form = new Form({
                    'starts_at':  "",
                    'duration': 0,
                    'caregiver_id': "",
                    'client_id': (this.client.id) ? this.client.id : "",
                    'caregiver_rate': "",
                    'provider_fee': "",
                    'notes': "",
                    'hours_type': "default",
                    'overtime_duration': 0,
                    'interval_type': "",
                    'recurring_end_date': "",
                    'bydays': [],
                    ...this.defaultValues
                });
            },

            setDateTimeFromEvent() {
                if (this.selectedEvent) {
                    this.startDate = this.selectedEvent.format('MM/DD/YYYY');
                    this.startTime = (this.selectedEvent._ambigTime) ? '09:00' : this.selectedEvent.format('HH:mm');
                    this.endTime = (this.selectedEvent._ambigTime) ? '10:00' : moment(this.selectedEvent).add(60, 'minutes').format('HH:mm');
                }
            },

            submitForm() {
                if (this.form.hours_type !== 'default') {
                    // Temporarily: Set overtime duration to duration
                    this.form.overtime_duration = this.duration;
                }

                if (!this.endDate) {
                    // Set end date to 2 years from now if empty
                    this.endDate = moment().add(2, 'years').format('MM/DD/YYYY');
                }

                // Fill/format form values
                this.form.duration = this.getDuration();
                this.form.starts_at = this.getStartsAt();
                this.form.recurring_end_date = moment(this.endDate + ' ' + this.startTime, 'MM/DD/YYYY HH:mm').add(1, 'minutes').format('X');

                // Submit form
                this.form.post('/business/schedule')
                    .then(response => {
                        this.refreshEvents();
                    })
                    .catch(error => {
                        this.handleErrors(error);
                    });
            },
        },

        watch: {
            model(val) {
                this.createModel = val;
                this.makeForm();
            },

            createModel(val) {
                this.createType = null;
                this.$emit('update:model', val);
                if (val) {
                    this.loadClientData();
                }
            },

            selectedEvent() {
                this.setDateTimeFromEvent();
            },
        },
    }
</script>