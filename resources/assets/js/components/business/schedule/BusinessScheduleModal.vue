<template>
    <div>
        <b-modal id="businessScheduleModal"
                 :title="title"
                 class="modal-fit-more"
                 size="lg"
                 :no-close-on-backdrop="true"
                 v-model="scheduleModal"
                 v-if="!maxHoursWarning"
        >
            <b-card no-body>
                <b-tabs card v-model="activeTab">
                    <b-tab title="Initial Shift" id="schedule-main">
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
                                <b-form-group>
                                    <div class="float-right">
                                        <b-btn variant="link" size="sm" @click="toggleCaregivers()">
                                            {{ toggleCaregiversLabel }}
                                        </b-btn>
                                    </div>
                                    <label for="caregiver_id">Assigned Caregiver</label>
                                    <b-form-select
                                            id="caregiver_id"
                                            name="caregiver_id"
                                            v-model="form.caregiver_id"
                                    >
                                        <option value="">--Not Assigned--</option>
                                        <option v-for="caregiver in caregivers" :value="caregiver.id">{{ caregiver.name }}</option>
                                    </b-form-select>
                                    <small v-if="cgMode == 'all'" class="form-text text-muted">
                                        <span class="text-danger">Caregivers that are not currently assigned to the client will use the rates below as their defaults upon saving.</span>
                                    </small>
                                    <input-help v-else :form="form" field="caregiver_id" text="Select the caregiver for this schedule." />
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
                    <b-tab title="Recurrence" id="schedule-recurrence" v-if="!selectedSchedule.id">
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
                                    <b-form-group label="End date" label-for="endDate">
                                        <date-picker v-model="endDate" />
                                        <input-help :form="form" field="recurring_end_date" text="Repeat the schedule until this date." />
                                    </b-form-group>
                                </div>
                            </b-col>
                        </b-row>
                    </b-tab>
                    <b-tab title="Notes" id="schedule-notes">
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
                <b-btn variant="info" @click="submitForm()" :disabled="submitting">
                    <i class="fa fa-spinner fa-spin" v-show="submitting"></i>
                    <i class="fa fa-save" v-show="!submitting"></i>
                    {{ submitText }}
                </b-btn>
                <b-btn variant="primary" @click="copySchedule()" v-show="selectedSchedule.id" class="mr-auto"><i class="fa fa-copy"></i> Copy</b-btn>
                <b-btn variant="danger" @click="deleteSchedule()" v-show="selectedSchedule.id" class="mr-auto"><i class="fa fa-times"></i> Delete</b-btn>
                <b-btn variant="default" @click="scheduleModal=false">Close</b-btn>
            </div>
        </b-modal>
        <b-modal id="maxHoursWarning" title="Schedule Shift" v-model="scheduleModal" v-else-if="maxHoursWarning">
            <b-container fluid>
                <h4>This will put the client over the maximum weekly hours.  Are you sure you want to do this?</h4>
            </b-container>
            <div slot="modal-footer">
                <b-btn variant="default" @click="scheduleModal=false">No, Cancel</b-btn>
                <b-btn variant="danger" @click="submitForm()" :disabled="submitting">
                    <i class="fa fa-spinner fa-spin" v-show="submitting"></i>
                    Yes, Save
                </b-btn>
            </div>
        </b-modal>
    </div>
</template>

<script>
    export default {
        props: {
            model: Boolean,
            initialValues: {
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
            },
            selectedSchedule: {
                type: Object,
                default() {
                    return {};
                }
            }
        },
        
        data() {
            return {
                activeTab: 0,
                submitting: false,
                startDate: "",
                startTime: "",
                endTime: "",
                endDate: "",
                scheduleModal: this.model,
                form: new Form(),
                copiedSchedule: {},
                allyPct: 0.05,
                paymentType: 'NONE',
                clientCaregivers: [],
                cgMode: 'client',
                allCaregivers: [],
                clients: [],
                daysOfWeek: {
                    'Sunday': 'su',
                    'Monday': 'mo',
                    'Tuesday': 'tu',
                    'Wednesday': 'we',
                    'Thursday': 'th',
                    'Friday': 'fr',
                    'Saturday': 'sa',
                },
                specialHoursChange: false,
                maxHoursWarning: false,
            }
        },

        mounted() {
            this.loadClientData();
            this.loadAllCaregivers();
        },

        computed: {
            title() {
                if (this.selectedSchedule.id) {
                    return 'Editing a Scheduled Shift';
                }
                if (!_.isEmpty(this.copiedSchedule)) {
                    return 'Copying Schedule';
                }
                return 'Schedule Shift';
            },

            submitText() {
                if (this.selectedSchedule.id) {
                    return 'Save';
                }
                return 'Create Schedule';
            },

            defaultValues() {
                if (this.copiedSchedule.starts_at) {
                    return {
                        'starts_at': this.copiedSchedule.starts_at,
                        'duration': this.copiedSchedule.duration,
                        'caregiver_id': this.copiedSchedule.caregiver_id,
                        'client_id': this.copiedSchedule.client_id,
                        'caregiver_rate': this.copiedSchedule.caregiver_rate,
                        'provider_fee': this.copiedSchedule.provider_fee,
                        'notes': this.copiedSchedule.notes,
                        'hours_type': this.copiedSchedule.hours_type,
                        'overtime_duration': this.copiedSchedule.overtime_duration,
                    }
                }
                return this.initialValues;
            },

            allyFee() {
                if (!parseFloat(this.form.caregiver_rate)) return null;
                let caregiverHourlyFloat = parseFloat(this.form.caregiver_rate);
                let providerHourlyFloat = parseFloat(this.form.provider_fee);
                let allyFee = (caregiverHourlyFloat + providerHourlyFloat) * parseFloat(this.allyPct);
                return allyFee.toFixed(2);
            },

            displayAllyPct() {
                return (parseFloat(this.allyPct) * 100).toFixed(2);
            },

            totalRate() {
                if (this.allyFee === null) return null;
                let caregiverHourlyFloat = parseFloat(this.form.caregiver_rate);
                let providerHourlyFloat = parseFloat(this.form.provider_fee);
                let totalRate = caregiverHourlyFloat + providerHourlyFloat + parseFloat(this.allyFee);
                return totalRate.toFixed(2);
            },

            selectedCaregiver() {
                if (this.form.caregiver_id) {
                    for(let index in this.caregivers) {
                        let caregiver = this.caregivers[index];
                        if (caregiver.id == this.form.caregiver_id) {
                            return caregiver;
                        }
                    }
                }
                return {
                    pivot: {}
                };
            },

            caregivers() {
                if (this.cgMode === 'all') {
                    return this.allCaregivers;
                }
                return this.clientCaregivers;
            },
            
            toggleCaregiversLabel() {
                if (this.cgMode === 'all') {
                    return "Show only Client's"
                } 
                return 'Show All';
            },
        },

        methods: {
            makeForm() {
                if (this.selectedSchedule.id) {
                    return this.makeEditForm();
                }
                return this.makeCreateForm();
            },

            makeCreateForm() {
                this.form = new Form({
                    'starts_at':  "",
                    'duration': 0,
                    'caregiver_id': "",
                    'client_id': "",
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
                if (this.form.starts_at) {
                    this.setDateTimeFromEvent(moment(this.form.starts_at, 'X'));
                }
            },

            makeEditForm() {
                this.form = new Form({
                    'starts_at': this.selectedSchedule.starts_at,
                    'duration': this.selectedSchedule.duration,
                    'caregiver_id': this.selectedSchedule.caregiver_id,
                    'client_id': this.selectedSchedule.client_id,
                    'caregiver_rate': this.selectedSchedule.caregiver_rate,
                    'provider_fee': this.selectedSchedule.provider_fee,
                    'notes': this.selectedSchedule.notes,
                    'hours_type': this.selectedSchedule.hours_type,
                    'overtime_duration': this.selectedSchedule.overtime_duration,
                });
                this.setDateTimeFromEvent(moment(this.selectedSchedule.starts_at, 'X'));
            },

            setDateTimeFromEvent(event = null) {
                if (!event) {
                    event = this.selectedEvent;
                }
                if (!this.form.duration) {
                    this.form.duration = 60;
                }
                if (event) {
                    event = moment(event);
                    this.startDate = event.format('MM/DD/YYYY');
                    this.startTime = (event._ambigTime) ? '09:00' : event.format('HH:mm');
                    this.endTime = (event._ambigTime) ? '10:00' : moment(event).add(this.form.duration, 'minutes').format('HH:mm');
                }
            },

            submitForm() {
                this.submitting = true;

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
                let url = '/business/schedule';
                let method = 'post';
                if (this.selectedSchedule.id) {
                    method = 'patch';
                    url = url + '/' + this.selectedSchedule.id;
                }
                this.form.submit(method, url)
                    .then(response => {
                        this.refreshEvents();
                        this.submitting = false;
                    })
                    .catch(error => {
                        this.handleErrors(error);
                        this.submitting = false;
                    });
            },

            copySchedule() {
                if (this.selectedSchedule.id) {
                    this.copiedSchedule = Object.assign({}, this.selectedSchedule);
                    this.selectedSchedule = {};
                    this.makeCreateForm();
                }
            },

            deleteSchedule() {
                if (this.selectedSchedule.id && confirm('Are you sure you wish to delete this scheduled shift?')) {
                    let form = new Form();
                    form.submit('delete', '/business/schedule/' + this.selectedSchedule.id)
                        .then(response => {
                            this.refreshEvents();
                        });
                }
            },

            dayOfMonth(date) {
                return moment(date).format('Do');
            },

            loadAllyPctFromClient(client_id) {
                let component = this;
                axios.get('/business/clients/' + client_id + '/payment_type').then(function(response) {
                    component.allyPct = response.data.percentage_fee;
                    component.paymentType = response.data.payment_type;
                });
            },

            loadCaregivers() {
                if (this.form.client_id) {
                    axios.get('/business/clients/' + this.form.client_id + '/caregivers')
                        .then(response => {
                            this.clientCaregivers = response.data;
                        });
                }
            },

            loadAllCaregivers() {
                axios.get('/business/caregivers?json=1')
                    .then(response => {
                        this.allCaregivers = response.data;
                    });
            },

            loadClientData() {
                if (!this.client_id) {
                    let component = this;
                    axios.get('/business/clients/list')
                        .then(response => {
                            component.clients = response.data;
                            this.loadCaregivers();
                        });
                }
                else {
                    // Load caregivers and ally pct immediately
                    this.loadCaregivers();
                    this.loadAllyPctFromClient(this.client_id);
                }
            },

            getDuration() {
                if (this.endTime && this.startTime) {
                    if (this.startTime === this.endTime) {
                        return 1440; // have 12:00am to 12:00am = 24 hours
                    }
                    let start = moment(this.startDate + ' ' + this.startTime, 'MM/DD/YYYY HH:mm');
                    let end = moment(this.startDate + ' ' + this.endTime, 'MM/DD/YYYY HH:mm');
                    console.log(start, end);
                    if (start && end) {
                        if (end.isBefore(start)) {
                            end = end.add(1, 'days');
                        }
                        let diff = end.diff(start, 'minutes');
                        if (diff) {
                            return parseInt(diff);
                        }
                    }
                }
                return null;
            },

            getStartsAt() {
                if (this.startDate && this.startTime) {
                    return moment(this.startDate + ' ' + this.startTime, 'MM/DD/YYYY HH:mm').format('X');
                }
                return null;
            },

            refreshEvents() {
                this.$emit('refresh-events');
                this.scheduleModal = false;
            },

            showMaxHoursWarning(response) {
                this.maxHoursWarning = true;
                // Recreate the form with max override
                let data = this.form.data();
                data.override_max_hours = 1;
                this.form = new Form(data);
            },

            hideMaxHoursWarning() {
                this.maxHoursWarning = false;
            },

            handleErrors(error) {
                if (error.response) {
                    switch(error.response.status) {
                        case 449:
                            this.showMaxHoursWarning(error.response);
                            break;
                    }
                }
            },

            resetTabs() {
                this.activeTab = 1;
                this.$nextTick(function() {
                    // (fix for tabs within tabs)
                    this.activeTab = 0;
                });
            },

            toggleCaregivers() {
                this.cgMode = this.cgMode === 'all' ? 'client' : 'all';
            },
        },

        watch: {
            model(val) {
                // Hide warning modal if hiding this modal
                if (!val) {
                    this.hideMaxHoursWarning();
                }

                // Force back to first tab
                this.resetTabs();

                // Clear copied values
                this.copiedSchedule = {};

                // Update local modal bool
                this.scheduleModal = val;

                // Re-create the form object
                this.makeForm();
            },

            scheduleModal(val) {
                this.createType = null;
                this.$emit('update:model', val);
                if (val) {
                    this.loadClientData();
                }
            },

            selectedEvent() {
                this.setDateTimeFromEvent();
            },

            'form.client_id': function(val) {
                this.cgMode = 'client';
                this.loadAllyPctFromClient(val);
                this.loadCaregivers();
            },

            'form.caregiver_id': function(val, old_val) {
                if (this.selectedSchedule) {
                    // Use the schedule's rates if the caregiver_id matches the schedule's caregiver_id
                    if (this.selectedSchedule.caregiver_id == val) {
                        this.form.caregiver_rate = this.selectedSchedule.caregiver_rate;
                        this.form.provider_fee = this.selectedSchedule.provider_fee
                        return;
                    }
                }

                if (this.cgMode === 'all') {
                    this.form.caregiver_rate = 0.00; 
                    this.form.provider_fee = 0.00;
                } else {
                    this.form.caregiver_rate = this.selectedCaregiver.pivot.caregiver_hourly_rate;
                    this.form.provider_fee = this.selectedCaregiver.pivot.provider_hourly_fee;
                }
            },

            'form.hours_type': function(val, old_val) {
                if (old_val) {
                    if (val === 'holiday' || val === 'overtime') {
                        this.specialHoursChange = true;
                        return;
                    }
                }
                this.specialHoursChange = false;
            },
        },
    }
</script>