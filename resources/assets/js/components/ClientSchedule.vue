<template>
    <b-card>
        <full-calendar ref="calendar" :events="events" @day-click="createSchedule" @event-selected="editSchedule" />


        <b-modal id="createScheduleModal" title="Create Schedule" v-model="createModal">
            <b-container fluid>
                <b-row>
                    <b-col lg="12">
                        <b-form-group label="Event Type" label-for="createType">
                            <b-form-select
                                    id="createType"
                                    name="createType"
                                    v-model="createType"
                            >
                                <option value="single">Single Occurrence</option>
                                <option value="recurring">Recurring Event</option>
                            </b-form-select>
                            <input-help :form="createForm" field="createType" text="Create one event or an event that repeats."></input-help>
                        </b-form-group>
                    </b-col>
                </b-row>
                <div v-if="createType">
                    <b-row>
                        <b-col lg="12">
                            <b-form-group label="Start Date" label-for="date">
                                <b-form-input
                                        id="date"
                                        name="date"
                                        type="text"
                                        v-model="createForm.start_date"
                                >
                                </b-form-input>
                                <input-help :form="createForm" field="date" text="Confirm the starting date."></input-help>
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
                                        v-model="createForm.time"
                                >
                                </b-form-select>
                                <input-help :form="createForm" field="time" text="Confirm the starting time."></input-help>
                            </b-form-group>
                        </b-col>
                        <b-col sm="6">
                            <b-form-group label="End Time" label-for="duration">
                                <b-form-select
                                        id="duration"
                                        name="duration"
                                        :options="endTimes"
                                        v-model="createForm.duration"
                                >
                                </b-form-select>
                                <input-help :form="createForm" field="duration" text="Confirm the ending time."></input-help>
                            </b-form-group>
                        </b-col>
                    </b-row>
                    <b-row>
                        <b-col sm="12">
                            <b-form-group label="Assigned Caregiver" label-for="caregiver_id">
                                <b-form-select
                                        id="caregiver_id"
                                        name="caregiver_id"
                                        v-model="createForm.caregiver_id"
                                        @input="updateDefaultRate()"
                                >
                                    <option value="">--Not Assigned--</option>
                                    <option v-for="caregiver in caregivers" :value="caregiver.id">{{ caregiver.name }}</option>
                                </b-form-select>
                                <input-help :form="createForm" field="caregiver_id" text="Select the caregiver for this schedule."></input-help>
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
                                        v-model="createForm.notes"
                                >
                                </b-form-textarea>
                                <input-help :form="createForm" field="notes" text="Enter any notes relating to this scheduled shift."></input-help>
                            </b-form-group>
                        </b-col>
                    </b-row>
                </div>
                <div v-if="createType == 'recurring'">
                    <b-row>
                        <b-col lg="12">
                            <b-form-group label="Recurring Period" label-for="interval_type">
                                <b-form-select
                                        id="interval_type"
                                        name="interval_type"
                                        v-model="createForm.interval_type"
                                >
                                    <option value="weekly">Weekly</option>
                                    <option value="biweekly">Bi-weekly</option>
                                    <option value="monthly">Monthly</option>
                                </b-form-select>
                                <input-help :form="createForm" field="interval_type" text="Select how often the schedule repeats."></input-help>
                            </b-form-group>
                            <div v-if="createForm.interval_type">
                                <div class="form-check" v-if="createForm.interval_type == 'weekly' || createForm.interval_type == 'biweekly'">
                                    <input-help :form="createForm" field="bydays" text="Select the days of the week below."></input-help>
                                    <label class="custom-control custom-checkbox" v-for="(item, index) in daysOfWeek">
                                        <input type="checkbox" class="custom-control-input" name="bydays[]" v-model="createForm.bydays" :value="item">
                                        <span class="custom-control-indicator"></span>
                                        <span class="custom-control-description">{{ index }}</span>
                                    </label>
                                </div>
                                <p v-else>
                                    The schedule will repeat every month on the {{ dayOfMonth(createForm.start_date) }}.
                                </p>
                            </div>
                            <b-form-group label="End date" label-for="end_date">
                                <b-form-input
                                        id="end_date"
                                        name="end_date"
                                        type="text"
                                        v-model="createForm.end_date"
                                >
                                </b-form-input>
                                <input-help :form="createForm" field="end_date" text="Repeat the schedule until this date."></input-help>
                            </b-form-group>
                        </b-col>
                    </b-row>
                </div>

            </b-container>
            <div slot="modal-footer">
                <b-btn variant="default" @click="createModal=false">Close</b-btn>
                <b-btn variant="info" @click="submitCreateForm()">Save</b-btn>
            </div>
        </b-modal>


        <b-modal id="editScheduleModal" title="Edit Schedule" v-model="editModal">
            <b-container fluid>
                <b-row  v-if="!selectedSchedule">
                    <b-col lg="12" class="text-center">
                        Loading Schedule Data..
                    </b-col>
               </b-row>
                <div v-else>
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
                                <input-help :form="editForm" field="editType" text="Select which type of modification you wish to make."></input-help>
                            </b-form-group>
                        </b-col>
                    </b-row>
                    <div v-if="editType">
                        <b-row>
                            <b-col lg="12">
                                <b-form-group label="Selected Date" label-for="date">
                                    <b-form-input
                                            id="date"
                                            name="date"
                                            type="text"
                                            v-model="editForm.selected_date"
                                            readonly
                                    >
                                    </b-form-input>
                                    <input-help :form="editForm" field="date" text="Confirm the date you wish to edit."></input-help>
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
                                            v-model="editForm.time"
                                    >
                                    </b-form-select>
                                    <input-help :form="editForm" field="time" text="Confirm the starting time."></input-help>
                                </b-form-group>
                            </b-col>
                            <b-col sm="6">
                                <b-form-group label="End Time" label-for="duration">
                                    <b-form-select
                                            id="duration"
                                            name="duration"
                                            :options="endTimes"
                                            v-model="editForm.duration"
                                    >
                                    </b-form-select>
                                    <input-help :form="editForm" field="duration" text="Confirm the ending time."></input-help>
                                </b-form-group>
                            </b-col>
                        </b-row>
                        <b-row>
                            <b-col sm="12">
                                <b-form-group label="Assigned Caregiver" label-for="caregiver_id">
                                    <b-form-select
                                        id="caregiver_id"
                                        name="caregiver_id"
                                        v-model="editForm.caregiver_id"
                                        @input="updateDefaultRate()"
                                        >
                                        <option value="">--Not Assigned--</option>
                                        <option v-for="caregiver in caregivers" :value="caregiver.id">{{ caregiver.name }}</option>
                                    </b-form-select>
                                    <input-help :form="editForm" field="caregiver_id" text="Select the caregiver for this schedule."></input-help>
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
                                            v-model="editForm.notes"
                                    >
                                    </b-form-textarea>
                                    <input-help :form="editForm" field="notes" text="Enter any notes relating to this scheduled shift."></input-help>
                                </b-form-group>
                            </b-col>
                        </b-row>
                    </div>
                    <div v-if="editType == 'all'">
                        <b-row>
                            <b-col lg="12">
                                <b-form-group label="Recurring Period" label-for="interval_type">
                                    <b-form-select
                                            id="interval_type"
                                            name="interval_type"
                                            v-model="editForm.interval_type"
                                    >
                                        <option value="weekly">Weekly</option>
                                        <option value="biweekly">Bi-weekly</option>
                                        <option value="monthly">Monthly</option>
                                    </b-form-select>
                                    <input-help :form="editForm" field="interval_type" text="Select how often the schedule repeats."></input-help>
                                </b-form-group>
                                <div class="form-check" v-if="editForm.interval_type == 'weekly' || editForm.interval_type == 'biweekly'">
                                    <input-help :form="editForm" field="bydays" text="Select the days of the week below."></input-help>
                                    <label class="custom-control custom-checkbox" v-for="(item, index) in daysOfWeek">
                                        <input type="checkbox" class="custom-control-input" name="bydays[]" v-model="editForm.bydays" :value="item">
                                        <span class="custom-control-indicator"></span>
                                        <span class="custom-control-description">{{ index }}</span>
                                    </label>
                                </div>
                                <p v-else>
                                    The schedule will repeat every month on the {{ dayOfMonth(editForm.selected_date) }}.
                                </p>
                                <b-form-group label="End date" label-for="end_date">
                                    <b-form-input
                                            id="end_date"
                                            name="end_date"
                                            type="text"
                                            v-model="editForm.end_date"
                                    >
                                    </b-form-input>
                                    <input-help :form="editForm" field="end_date" text="Repeat the schedule until this date."></input-help>
                                </b-form-group>
                            </b-col>
                        </b-row>
                    </div>
                </div>
            </b-container>
            <div slot="modal-footer">
                <b-btn variant="default" @click="editModal=false">Close</b-btn>
                <b-btn variant="info" @click="submitEditForm()">Save</b-btn>
                <b-btn variant="danger" @click="deleteSingleEvent()" v-if="editType == 'single'">Delete Single Event</b-btn>
                <b-btn variant="danger" @click="deleteAllEvents()" v-if="editType == 'all'">Delete ALL Events</b-btn>
            </div>
        </b-modal>
    </b-card>
</template>

<script>
    export default {
        props: {
            client: {},
            schedules: {},
        },

        data() {
            return {
                events: '/business/clients/' + this.client.id + '/schedule',
                createModal: false,
                editModal: false,
                selectedSchedule: null,
                selectedEvent: null,
                editForm: new Form(),
                createForm: new Form(),
                editType: null,
                createType: null,
                interval: 15, // number of minutes in between each time period
                daysOfWeek: {
                    'Sunday': 'su',
                    'Monday': 'mo',
                    'Tuesday': 'tu',
                    'Wednesday': 'we',
                    'Thursday': 'th',
                    'Friday': 'fr',
                    'Saturday': 'sa',
                },
                caregivers: {},
            }
        },

        mounted() {
            this.resetEdit();
            console.log(this.$refs);
        },

        methods: {

            createSchedule(date, jsEvent, view) {
                this.loadCaregivers();
                this.createModal = true;
                this.createType = null;
                this.selectedEvent = date;
            },

            dayOfMonth(date) {
               return moment(date).format('Do');
            },

            editSchedule(event, jsEvent, view) {
                var component = this;
                component.loadCaregivers();
                component.resetEdit();
                component.selectedEvent = event;
                axios.get(this.events + '/' + event.id)
                    .then(function(response) {
                        console.log(response.data);
                        component.selectedSchedule = response.data;
                        component.editModal = true;
                    })
                    .catch(function(error) {
                        alert('Error loading schedule details');
                    });

            },

            resetEdit() {
                this.editForm = new Form();
                this.selectedSchedule = null;
                this.editType = null;
            },

            makeCreateSingleForm() {
                this.createForm = new Form({
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
                this.createForm = new Form({
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

            makeEditSingleForm() {
                this.editForm = new Form({
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
                this.editForm = new Form({
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
            },

            submitCreateForm() {
                var component = this;
                if (this.createType == 'single') {
                    this.createForm.post('/business/clients/' + this.client.id + '/schedule/single')
                        .then(function(response) {
                            component.refreshEvents();
                        });
                }
                else {
                    this.createForm.post('/business/clients/' + this.client.id + '/schedule')
                        .then(function(response) {
                            component.refreshEvents();
                        });
                }
            },

            submitEditForm() {
                var component = this;
                if (this.editType == 'single') {
                    this.editForm.patch('/business/clients/' + this.client.id + '/schedule/' + + this.selectedSchedule.id + '/single')
                        .then(function(response) {
                            component.refreshEvents();
                        });
                }
                else {
                    this.editForm.patch('/business/clients/' + this.client.id + '/schedule/' + this.selectedSchedule.id)
                        .then(function(response) {
                            component.refreshEvents();
                        });
                }
            },

            deleteSingleEvent() {
                if (!confirm('Are you sure you wish to delete this single event?')) {
                    return;
                }
                let component = this;
                let deleteForm = new Form({
                    selected_date: this.editForm.selected_date,
                    utc_offset: this.getUserUtcOffset(),
                });
                deleteForm.post('/business/clients/' + this.client.id + '/schedule/' + + this.selectedSchedule.id + '/single/delete')
                    .then(function(response) {
                        component.refreshEvents();
                    });
            },

            deleteAllEvents() {
                if (!confirm('Are you sure you wish to delete this entire schedule on and after ' + this.editForm.selected_date + '?')) {
                    return;
                }
                let component = this;
                let deleteForm = new Form({
                    selected_date: this.editForm.selected_date,
                    utc_offset: this.getUserUtcOffset(),
                });
                deleteForm.post('/business/clients/' + this.client.id + '/schedule/' + this.selectedSchedule.id + '/delete')
                    .then(function(response) {
                        component.refreshEvents();
                    });
            },

            refreshEvents(hideModals = true) {
                this.$refs.calendar.fireMethod('refetchEvents');
                if (hideModals) {
                    this.createModal = false;
                    this.editModal = false;
                }
            },

            updateDefaultRate() {
                let form = this.createForm;
                if (this.editModal) {
                    form = this.editForm;
                }
                if (!form.caregiver_id) {
                    form.scheduled_rate = null;
                    return;
                }
                for (let key in this.caregivers) {
                    let caregiver = this.caregivers[key];
                    if (caregiver.id == form.caregiver_id) {
                        form.scheduled_rate = caregiver.default_rate;
                    }
                }
            },

            getUserUtcOffset() {
                return moment().local().format('Z');
            },

            getLocalMomentObject(server_date, server_time) {
                let timestamp = server_date + 'T' + server_time + '+00:00';
                console.log(timestamp);
                let obj = moment(timestamp).local();
                console.log(obj);
                return obj;
            },

            loadCaregivers() {
                axios.get('/business/clients/' + this.client.id + '/caregivers')
                    .then(response => this.caregivers = response.data);
            }
        },

        watch: {
            editType(val) {
                if (val == 'single') this.makeEditSingleForm();
                else this.makeEditAllForm();
            },
            createType(val) {
                if (val == 'single') this.makeCreateSingleForm();
                else this.makeCreateRecurringForm()
            },
        },

        computed: {

            startTimes() {
                let date = moment('01/01/2000 00:00:00');
                let rounds = Math.ceil(1440 / this.interval);
                let startTimes = [];
                for (let i = 0; i<rounds; i++) {
                    startTimes.push({
                        value: date.format('HH:mm:ss'),
                        text: date.format('LT')
                    });
                    date.add(this.interval, 'minutes');
                }
                return startTimes;
            },

            endTimes() {
                let date = null;
                if (this.editModal) {
                    date = moment('01/01/2000 ' + this.editForm.time);
                }
                else {
                    date = moment('01/01/2000 ' + this.createForm.time);
                }
                let rounds = Math.ceil(1440 / this.interval);
                let endTimes = [];
                for (let i = 0; i<rounds; i++) {
                    endTimes.push({
                        value: i * this.interval,
                        text: date.format('LT')
                    });
                    date.add(this.interval, 'minutes');
                }
                return endTimes;
            }

        }
    }
</script>