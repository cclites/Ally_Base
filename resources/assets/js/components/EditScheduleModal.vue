<template>
    <b-modal id="editScheduleModal" title="Edit Schedule" v-model="editModel">
        <b-container fluid>
            <b-row v-if="!selectedSchedule">
                <b-col lg="12" class="text-center">
                    Loading Schedule Data..
                </b-col>
            </b-row>
            <div v-hide="!selectedSchedule">
                <b-row v-if="!this.client">
                    <b-col lg="12">
                        <b-form-group label="Client" label-for="client_id">
                            <b-form-select
                                id="client_id"
                                name="client_id"
                                v-model="client_id"
                                disabled
                                >
                                <option v-for="item in clients" :value="item.id">{{ item.name }}</option>
                            </b-form-select>
                            <input-help :form="form" field="client_id" text=""></input-help>
                        </b-form-group>
                    </b-col>
                </b-row>
                <b-row>
                    <b-col lg="12">
                        <b-form-group label="Edit Choice" label-for="editType">
                            <b-form-select
                                    id="editType"
                                    name="editType"
                                    v-model="editType"
                            >
                                <option value="single">Single Occurrence</option>
                                <option value="all">All Future Events</option>
                            </b-form-select>
                            <input-help :form="form" field="editType" text="Select which type of modification you wish to make."></input-help>
                        </b-form-group>
                    </b-col>
                </b-row>
                <div v-show="editType">
                    <b-row>
                        <b-col lg="12">
                            <b-form-group label="Selected Date" label-for="date">
                                <b-form-input
                                        id="date"
                                        name="date"
                                        type="text"
                                        v-model="form.selected_date"
                                        readonly
                                >
                                </b-form-input>
                                <input-help :form="form" field="date" text="Confirm the date you wish to edit."></input-help>
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
                                        @input="updateDefaultRate()"
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
                <div v-show="editType == 'all'">
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
                            <div class="form-check" v-if="form.interval_type == 'weekly' || form.interval_type == 'biweekly'">
                                <input-help :form="form" field="bydays" text="Select the days of the week below."></input-help>
                                <label class="custom-control custom-checkbox" v-for="(item, index) in daysOfWeek">
                                    <input type="checkbox" class="custom-control-input" name="bydays[]" v-model="form.bydays" :value="item">
                                    <span class="custom-control-indicator"></span>
                                    <span class="custom-control-description">{{ index }}</span>
                                </label>
                            </div>
                            <p v-else>
                                The schedule will repeat every month on the {{ dayOfMonth(form.selected_date) }}.
                            </p>
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
            </div>
        </b-container>
        <div slot="modal-footer">
            <b-btn variant="default" @click="editModel=false">Close</b-btn>
            <b-btn variant="info" @click="submitForm()">Save</b-btn>
            <b-btn variant="danger" @click="deleteSingleEvent()" v-if="editType == 'single'">Delete Single Event</b-btn>
            <b-btn variant="danger" @click="deleteAllEvents()" v-if="editType == 'all'">Delete ALL Events</b-btn>
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
            selectedSchedule: {},
        },
        
        data() {
            return {
                editModel: this.model,
                editType: null,
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
            deleteSingleEvent() {
                if (!confirm('Are you sure you wish to delete this single event?')) {
                    return;
                }
                let component = this;
                let deleteForm = new Form({
                    selected_date: this.form.selected_date,
                    utc_offset: this.getUserUtcOffset(),
                });
                deleteForm.post('/business/clients/' + this.client.id + '/schedule/' + + this.selectedSchedule.id + '/single/delete')
                    .then(function(response) {
                        component.refreshEvents();
                    });
            },

            deleteAllEvents() {
                if (!confirm('Are you sure you wish to delete this entire schedule on and after ' + this.form.selected_date + '?')) {
                    return;
                }
                let component = this;
                let deleteForm = new Form({
                    selected_date: this.form.selected_date,
                    utc_offset: this.getUserUtcOffset(),
                });
                deleteForm.post('/business/clients/' + this.client.id + '/schedule/' + this.selectedSchedule.id + '/delete')
                    .then(function(response) {
                        component.refreshEvents();
                    });
            },

            makeEditSingleForm() {
                this.form = new Form({
                    selected_date: moment(this.selectedEvent.start).format('L'),
                    time: this.getLocalMomentObject(this.selectedSchedule.start_date, this.selectedSchedule.time).format('HH:mm:ss'),
                    duration: this.selectedSchedule.duration,
                    caregiver_id: this.selectedSchedule.caregiver_id,
//                    scheduled_rate: this.selectedSchedule.scheduled_rate,
                    notes: this.selectedSchedule.notes,
                    utc_offset: this.getUserUtcOffset(),
                });
            },

            makeEditAllForm() {
                this.form = new Form({
                    selected_date: moment(this.selectedEvent.start).format('L'),
                    end_date: this.getLocalMomentObject(this.selectedSchedule.end_date, this.selectedSchedule.time).format('L'),
                    time: this.getLocalMomentObject(this.selectedSchedule.start_date, this.selectedSchedule.time).format('HH:mm:ss'),
                    duration: this.selectedSchedule.duration,
                    interval_type: this.selectedSchedule.interval_type,
                    bydays: this.selectedSchedule.bydays,
                    caregiver_id: this.selectedSchedule.caregiver_id,
//                    scheduled_rate: this.selectedSchedule.scheduled_rate,
                    notes: this.selectedSchedule.notes,
                    utc_offset: this.getUserUtcOffset(),
                });

                if (this.form.end_date == '12/31/2100') {
                    this.form.end_date = null;
                }
            },


            submitForm() {
                var component = this;
                if (this.editType == 'single') {
                    this.form.patch('/business/clients/' + this.client_id + '/schedule/' + + this.selectedSchedule.id + '/single')
                        .then(function(response) {
                            component.refreshEvents();
                        });
                }
                else {
                    this.form.patch('/business/clients/' + this.client_id + '/schedule/' + this.selectedSchedule.id)
                        .then(function(response) {
                            component.refreshEvents();
                        });
                }
            },

        },

        watch: {
            model(val) {
                this.editModel = val;
            },

            editModel(val) {
                this.editType = null;
                this.$emit('update:model', val);
            },

            editType(val) {
                if (val == 'single') this.makeEditSingleForm();
                else this.makeEditAllForm();
            },

            'selectedSchedule.client_id': function(val) {
                this.client_id = val;
            }
        },

        mixins: [
            ScheduleForm
        ]
    }
</script>