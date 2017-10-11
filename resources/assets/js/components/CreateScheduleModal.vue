<template>
    <b-modal id="createScheduleModal" title="Create Schedule" v-model="createModel">
        <b-container fluid>
            <b-row v-if="!this.client">
                <b-col lg="12">
                    <b-form-group label="Client" label-for="client_id">
                        <b-form-select
                                id="client_id"
                                name="client_id"
                                v-model="client_id"
                        >
                            <option value="">--Select a Client--</option>
                            <option v-for="item in clients" :value="item.id">{{ item.name }}</option>
                        </b-form-select>
                        <input-help :form="form" field="client_id" text=""></input-help>
                    </b-form-group>
                </b-col>
            </b-row>
            <b-row>
                <b-col lg="12">
                    <b-form-group label="Event Type" label-for="createType">
                        <b-form-select
                                id="createType"
                                name="createType"
                                v-model="createType"
                        >
                            <option value="">--Select a Type--</option>
                            <option value="single">Single Occurrence</option>
                            <option value="recurring">Recurring Event</option>
                        </b-form-select>
                        <input-help :form="form" field="createType" text="Create one event or an event that repeats."></input-help>
                    </b-form-group>
                </b-col>
            </b-row>
            <div v-show="createType && client_id">
                <b-row>
                    <b-col lg="12">
                        <b-form-group label="Start Date" label-for="date">
                            <b-form-input
                                    id="date"
                                    name="date"
                                    type="text"
                                    v-model="form.start_date"
                                    class="datepicker"
                            >
                            </b-form-input>
                            <input-help :form="form" field="date" text="Confirm the starting date."></input-help>
                        </b-form-group>
                    </b-col>
                </b-row>
                <b-row>
                    <b-col sm="6">
                        <b-form-group label="Start Time" label-for="time">
                            <b-form-select
                                    id="time"
                                    name="time"
                                    :options="startTimes"
                                    v-model="form.time"
                            >
                            </b-form-select>
                            <input-help :form="form" field="time" text="Confirm the starting time."></input-help>
                        </b-form-group>
                    </b-col>
                    <b-col sm="6">
                        <b-form-group label="End Time" label-for="duration">
                            <b-form-select
                                    id="duration"
                                    name="duration"
                                    :options="endTimes"
                                    v-model="form.duration"
                            >
                            </b-form-select>
                            <input-help :form="form" field="duration" text="Confirm the ending time."></input-help>
                        </b-form-group>
                    </b-col>
                </b-row>
                <b-row>
                    <b-col sm="12">
                        <b-form-group label="Assigned Caregiver" label-for="caregiver_id">
                            <b-form-select
                                    id="caregiver_id"
                                    name="caregiver_id"
                                    v-model="form.caregiver_id"
                            >
                                <option value="">--Not Assigned--</option>
                                <option v-for="caregiver in caregivers" :value="caregiver.id">{{ caregiver.name }}</option>
                            </b-form-select>
                            <input-help :form="form" field="caregiver_id" text="Select the caregiver for this schedule."></input-help>
                        </b-form-group>
                    </b-col>
                </b-row>
                <b-row>
                    <b-col lg="12">
                        <b-form-group label="Schedule Notes" label-for="notes">
                            <b-form-textarea
                                    id="notes"
                                    name="notes"
                                    :rows="3"
                                    v-model="form.notes"
                            >
                            </b-form-textarea>
                            <input-help :form="form" field="notes" text="Enter any notes relating to this scheduled shift."></input-help>
                        </b-form-group>
                    </b-col>
                </b-row>
            </div>
            <div v-show="createType == 'recurring'">
                <b-row>
                    <b-col lg="12">
                        <b-form-group label="Recurring Period" label-for="interval_type">
                            <b-form-select
                                    id="interval_type"
                                    name="interval_type"
                                    v-model="form.interval_type"
                            >
                                <option value="weekly">Weekly</option>
                                <option value="biweekly">Bi-weekly</option>
                                <option value="monthly">Monthly</option>
                            </b-form-select>
                            <input-help :form="form" field="interval_type" text="Select how often the schedule repeats."></input-help>
                        </b-form-group>
                        <div v-if="form.interval_type">
                            <div class="form-check" v-if="form.interval_type == 'weekly' || form.interval_type == 'biweekly'">
                                <input-help :form="form" field="bydays" text="Select the days of the week below."></input-help>
                                <label class="custom-control custom-checkbox" v-for="(item, index) in daysOfWeek">
                                    <input type="checkbox" class="custom-control-input" name="bydays[]" v-model="form.bydays" :value="item">
                                    <span class="custom-control-indicator"></span>
                                    <span class="custom-control-description">{{ index }}</span>
                                </label>
                            </div>
                            <p v-else>
                                The schedule will repeat every month on the {{ dayOfMonth(form.start_date) }}.
                            </p>
                        </div>
                        <b-form-group label="End date" label-for="end_date">
                            <b-form-input
                                    id="end_date"
                                    name="end_date"
                                    type="text"
                                    v-model="form.end_date"
                                    class="datepicker"
                            >
                            </b-form-input>
                            <input-help :form="form" field="end_date" text="Repeat the schedule until this date."></input-help>
                        </b-form-group>
                    </b-col>
                </b-row>
            </div>

        </b-container>
        <div slot="modal-footer">
            <b-btn variant="default" @click="createModel=false">Close</b-btn>
            <b-btn variant="info" @click="submitForm()">Save</b-btn>
        </div>
    </b-modal>
</template>

<script>
    import ScheduleForm from '../mixins/ScheduleForm';

    export default {
     
        props: {
            client: {},
            model: {},
            selectedEvent: {},
        },
        
        data() {
            return {
                createModel: this.model,
                createType: null,
                form: new Form(),
            }
        },

        mounted() {
            jQuery('.datepicker').datepicker({
                autoclose: true,
                todayHighlight: true
            });
        },

        methods: {
            makeCreateSingleForm() {
                this.form = new Form({
                    start_date: this.selectedEvent.format('L'),
                    time: (this.selectedEvent._ambigTime) ? '09:00:00' : this.selectedEvent.format('HH:mm:ss'),
                    duration: 60,
                    caregiver_id: null,
//                    scheduled_rate: null,
                    notes: null,
                    utc_offset: this.getUserUtcOffset(),
                });
            },

            makeCreateRecurringForm() {
                this.form = new Form({
                    start_date: this.selectedEvent.format('L'),
                    end_date: null,
                    time: (this.selectedEvent._ambigTime) ? '09:00:00' : this.selectedEvent.format('HH:mm:ss'),
                    duration: 60,
                    interval_type: null,
                    bydays: [],
                    caregiver_id: null,
//                    scheduled_rate: null,
                    notes: null,
                    utc_offset: this.getUserUtcOffset(),
                });
            },


            submitForm() {
                var component = this;
                if (this.createType == 'single') {
                    this.form.post('/business/clients/' + this.client_id + '/schedule/single')
                        .then(function(response) {
                            component.refreshEvents();
                        });
                }
                else {
                    this.form.post('/business/clients/' + this.client_id + '/schedule')
                        .then(function(response) {
                            component.refreshEvents();
                        });
                }
            },
        },

        watch: {
            model(val) {
                this.createModel = val;
            },
            createModel(val) {
                this.createType = null;
                this.$emit('update:model', val);
            },
            createType(val) {
                if (val == 'single') this.makeCreateSingleForm();
                else this.makeCreateRecurringForm()
            },
        },

        mixins: [
            ScheduleForm
        ]
    }
</script>