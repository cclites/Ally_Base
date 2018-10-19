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
            <loading-card text="Loading details" v-show="isLoading"></loading-card>
            <b-card no-body v-if="!isLoading">
                <b-tabs card v-model="activeTab" ref="tabs">
                    <b-tab title="Shift Details" id="schedule-main">
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
                                        <option v-for="item in clients" :value="item.id" :key="item.id">{{ item.nameLastFirst }}</option>
                                    </b-form-select>
                                    <input-help :form="form" field="client_id" text="Select the client for this schedule." />
                                </b-form-group>
                            </b-col>
                            <b-col sm="6">
                                <b-form-group>
                                    <div class="float-right">
                                        <b-btn variant="link" size="sm" @click="toggleCaregivers()">
                                            {{ toggleCaregiversLabel }}
                                        </b-btn> |
                                        <b-btn variant="link" size="sm" @click="openCareMatchTab()">
                                            Find Caregivers
                                        </b-btn>
                                    </div>
                                    <label for="caregiver_id">Caregiver</label>
                                    <b-form-select
                                            id="caregiver_id"
                                            name="caregiver_id"
                                            v-model="form.caregiver_id"
                                    >
                                        <option value="">--Not Assigned--</option>
                                        <option v-for="caregiver in caregivers" :value="caregiver.id" :key="caregiver.id">{{ caregiver.nameLastFirst }}</option>
                                    </b-form-select>
                                    <small v-if="cgMode == 'all' && !selectedCaregiver.id" class="form-text text-muted">
                                        <span class="text-danger">Caregivers that are not currently assigned to the client will use the rates below as their defaults upon saving.</span>
                                    </small>
                                    <input-help v-else :form="form" field="caregiver_id" text="Select the caregiver for this schedule." />
                                </b-form-group>
                            </b-col>
                        </b-row>
                        <b-row>
                            <b-col>
                                <strong>Shift Type: </strong>
                                <input name="daily_rates" v-model="form.daily_rates" type="radio" class="with-gap" id="create_hourly_rates" :value="0">
                                <label for="create_hourly_rates" class="rate-label">Hourly</label>
                                <input name="daily_rates" v-model="form.daily_rates" type="radio" class="with-gap" id="create_daily_rates" :value="1">
                                <label for="create_daily_rates" class="rate-label">Daily</label>
                            </b-col>
                        </b-row>
                        <b-row v-show="form.daily_rates !== null">
                            <b-col sm="6">
                                <b-form-group :label="`Caregiver ${rateType} Rate`" label-for="caregiver_rate">
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
                                <b-form-group :label="`Provider ${rateType} Fee`" label-for="provider_fee">
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
                            <b-col sm="6">
                                <b-form-group label="Ally Fee" label-for="ally_fee">
                                    <div v-if="allyFee">
                                        {{ allyFee }}&nbsp;&nbsp;(Payment Type: {{ paymentType }} {{ displayAllyPct }}%)
                                    </div>
                                    <div v-else>
                                        Enter Caregiver and Provider Rates
                                    </div>
                                </b-form-group>
                            </b-col>
                            <b-col sm="6">
                                <b-form-group :label="`Total ${rateType} Rate`" label-for="ally_fee">
                                    {{ totalRate }}
                                </b-form-group>
                            </b-col>
                        </b-row>
                        <b-row>
                            <b-col lg="6">
                                <b-form-group label="Start Date" label-for="startDate">
                                    <date-picker v-model="startDate" />
                                    <input-help :form="form" field="starts_at" text="Confirm the starting date." />
                                </b-form-group>
                            </b-col>
                            <b-col lg="6">
                                <b-form-group label="End Date" label-for="startDate" v-if="firstShiftEndDate !== startDate">
                                    <date-picker v-model="firstShiftEndDate" disabled />
                                    <input-help :form="form" field="zzzz" text="The end date is shown when it differs from the start date." />
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
                                <b-form-group :label="`End Time - ${scheduledHours} Hours`" label-for="endTime">
                                    <time-picker
                                            id="endTime"
                                            name="endTime"
                                            v-model="endTime"
                                            :readonly="!!form.daily_rates"
                                    />
                                    <input-help :form="form" field="duration" text="Confirm the ending time." v-if="!form.daily_rates" />
                                    <input-help :form="form" field="duration" text="End time is locked when daily rates are set." v-else />
                                </b-form-group>
                            </b-col>
                        </b-row>
                        <b-row>
                            <b-col sm="6">
                                <b-form-group label="Shift Designation" label-for="hours_type">
                                    <b-form-radio-group id="hours_type" v-model="form.hours_type" name="hours_type">
                                        <b-form-radio value="default">Regular</b-form-radio>
                                        <b-form-radio value="holiday">Holiday</b-form-radio>
                                        <b-form-radio value="overtime">Overtime</b-form-radio>
                                    </b-form-radio-group>
                                    
                                    <input-help :form="form" field="hours_type" text="" />
                                    <small class="form-text text-info" v-if="specialHoursChange">
                                        Be sure to update the caregiver's rates to reflect this designation.
                                    </small>
                                </b-form-group>
                            </b-col>
                            <b-col sm="6">
                                <b-form-group label="Care Plan Requested by Client" label-for="care_plan_id">
                                    <b-form-select
                                            id="care_plan_id"
                                            name="care_plan_id"
                                            v-model="form.care_plan_id"
                                    >
                                        <option value="">--No Care Plan--</option>
                                        <option v-for="item in care_plans" :value="item.id" :key="item.id">{{ item.name }}</option>
                                    </b-form-select>
                                    <input-help :form="form" field="care_plan_id" text="" />
                                </b-form-group>
                            </b-col>
                        </b-row>
                    </b-tab>
                    <b-tab title="Recurrence" id="schedule-recurrence" v-if="!schedule.id">
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
                                        <label class="custom-control custom-checkbox" v-for="(item, index) in daysOfWeek" :key="item">
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
                                <b-form-group label="Schedule Status" label-for="status" v-if="schedule.id">
                                    <b-form-select
                                            id="status"
                                            name="status"
                                            v-model="form.status"
                                    >
                                        <option value="OK">No Status</option>
                                        <option value="ATTENTION_REQUIRED">Attention Required</option>
                                        <option value="CLIENT_CANCELED">Client Canceled</option>
                                        <option value="CAREGIVER_CANCELED">Caregiver Canceled</option>
                                    </b-form-select>
                                </b-form-group>
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
                    <b-tab title="Care Match" button-id="care-match-tab">
                        <business-care-match :clients="clients" :schedule="careMatchSchedule">
                            <template scope="row">
                                <b-button size="sm" variant="info" @click="selectCaregiver(row.item.id)">Select Caregiver</b-button>
                            </template>
                        </business-care-match>
                    </b-tab>
                </b-tabs>
            </b-card>

            <div slot="modal-footer" v-if="!isLoading">
                <b-btn variant="info" @click="submitForm()" :disabled="submitting">
                    <i class="fa fa-spinner fa-spin" v-show="submitting"></i>
                    <i class="fa fa-save" v-show="!submitting"></i>
                    {{ submitText }}
                </b-btn>
                <b-btn variant="primary" @click="copySchedule()" v-show="schedule.id" class="mr-auto"><i class="fa fa-copy"></i> Copy</b-btn>
                <b-btn v-show="schedule.clocked_in_shift" variant="warning" @click="clockOut()">Clock Out Shift</b-btn>
                <b-btn variant="danger" @click="deleteSchedule()" v-show="schedule.id" class="mr-auto"><i class="fa fa-times"></i> Delete</b-btn>
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
    import FormatsNumbers from "../../../mixins/FormatsNumbers";

    export default {
        mixins: [FormatsNumbers],

        props: {
            model: Boolean,
            passClients: {
                type: Array,
                required: true,
            },
            passCaregivers: {
                type: Array,
                required: true,
            },
            selectedSchedule: {
                type: Object,
                default() {
                    return {};
                }
            },
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
                care_plans: [],
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
        },

        computed: {
            title() {
                if (this.copiedSchedule.starts_at) {
                    return 'Copying Schedule';
                }
                if (this.selectedSchedule.id) {
                    return 'Editing a Scheduled Shift';
                }
                return 'Schedule Shift';
            },

            submitText() {
                if (this.selectedSchedule.id) {
                    return 'Save';
                }
                return 'Create Schedule';
            },

            isLoading() {
                return _.isEmpty(this.selectedSchedule);
            },

            schedule() {
                if (this.copiedSchedule.starts_at) return this.copiedSchedule;
                return this.selectedSchedule;
            },

            allyFee() {
                let caregiverHourlyFloat = parseFloat(this.form.caregiver_rate);
                let providerHourlyFloat = parseFloat(this.form.provider_fee);
                if (isNaN(caregiverHourlyFloat) || isNaN(providerHourlyFloat)) {
                    return false;
                }
                let allyFee = (caregiverHourlyFloat + providerHourlyFloat) * parseFloat(this.allyPct);
                return allyFee.toFixed(2);
            },

            displayAllyPct() {
                return (parseFloat(this.allyPct) * 100).toFixed(2);
            },

            totalRate() {
                let caregiverHourlyFloat = parseFloat(this.form.caregiver_rate);
                let providerHourlyFloat = parseFloat(this.form.provider_fee);
                if (isNaN(caregiverHourlyFloat) || isNaN(providerHourlyFloat)) {
                    return 'Enter Caregiver and Provider Rates'
                }
                let totalRate = caregiverHourlyFloat + providerHourlyFloat + parseFloat(this.allyFee);
                return totalRate.toFixed(2);
            },

            selectedCaregiver() {
                if (this.form.caregiver_id) {
                    for(let index in this.clientCaregivers) {
                        let caregiver = this.clientCaregivers[index];
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
                    return this.passCaregivers;
                }
                return this.clientCaregivers;
            },

            clients() {
                return this.passClients;
            },

            toggleCaregiversLabel() {
                if (this.cgMode === 'all') {
                    return "Show only Client's"
                }
                return 'Show All';
            },

            rateType() {
                if (this.form.daily_rates === 0) {
                    return 'Hourly';
                }
                if (this.form.daily_rates === 1) {
                    return 'Daily';
                }
                return '';
            },

            firstShiftEndDate() {
                let duration = this.getDuration();
                return moment(this.startDate + ' ' + this.startTime, 'MM/DD/YYYY HH:mm')
                    .add(duration, 'minutes')
                    .format('MM/DD/YYYY');
            },

            scheduledHours() {
                if (this.form.duration) {
                    return this.numberFormat(parseInt(this.form.duration) / 60);
                }
                return 0;
            },

            careMatchSchedule() {
                return {
                    starts_at: moment(this.startDate + ' ' + this.startTime, 'MM/DD/YYYY HH:mm').format('YYYY-MM-DD HH:mm:ss'),
                    duration: this.form.duration,
                    client_id: this.form.client_id,
                }
            },
        },

        methods: {

            selectCaregiver(id) {
                this.cgMode = 'all';
                this.form.caregiver_id = id;
                this.activeTab = 0;
            },

            openCareMatchTab() {
               this.activeTab = this.$refs.tabs.tabs.length - 1;
            },

            makeForm() {
                this.form = new Form({
                    'starts_at': this.schedule.starts_at || "",
                    'duration': this.schedule.duration || 0,
                    'caregiver_id': this.schedule.caregiver_id || "",
                    'client_id': this.schedule.client_id || "",
                    'daily_rates': this.schedule.daily_rates || 0,
                    'caregiver_rate': this.schedule.caregiver_rate || "",
                    'provider_fee': this.schedule.provider_fee || "",
                    'notes': this.schedule.notes || "",
                    'hours_type': this.schedule.hours_type || "default",
                    'overtime_duration': this.schedule.overtime_duration || 0,
                    'care_plan_id': this.schedule.care_plan_id || '',
                    'status': this.schedule.status || 'OK',
                    'interval_type': "",
                    'recurring_end_date': "",
                    'bydays': [],
                    'care_plan_id': "",
                });
                this.setDateTimeFromSchedule();
            },

            setDateTimeFromSchedule() {
                let start = moment(this.schedule.starts_at, 'YYYY-MM-DD HH:mm:ss');
                this.startDate = start.format('MM/DD/YYYY');
                this.startTime = (start._ambigTime) ? '09:00' : start.format('HH:mm');
                let end = moment(start).add(this.form.duration || 60, 'minutes');
                this.endTime = (end._ambigTime) ? '10:00' : end.format('HH:mm');
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
                if (this.schedule.id) {
                    method = 'patch';
                    url = url + '/' + this.schedule.id;
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
                    Vue.delete(this.copiedSchedule, 'id');
                    this.makeForm();
                }
            },

            clockOut() {
                this.scheduleModal = false;
                this.$emit('clock-out');
            },

            deleteSchedule() {
                if (this.schedule.id && confirm('Are you sure you wish to delete this scheduled shift?')) {
                    let form = new Form();
                    form.submit('delete', '/business/schedule/' + this.schedule.id)
                        .then(response => {
                            this.refreshEvents();
                        });
                }
            },

            dayOfMonth(date) {
                return moment(date).format('Do');
            },

            loadAllyPctFromClient(client_id) {
                if (!client_id) return;
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
                            this.prefillRates();
                        });
                }
            },

            loadClientData() {
                if (this.client_id) {
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

            loadCarePlans(client_id, old_val) {
                if (this.form.care_plan_id && old_val) {
                    this.form.care_plan_id = '';
                }
                let index = this.clients.findIndex(item => item.id == client_id);
                if (index > -1) {
                    this.care_plans = this.clients[index].care_plans;
                    return;
                }
                this.care_plans = [];
            },

            toggleCaregivers() {
                this.cgMode = this.cgMode === 'all' ? 'client' : 'all';
            },

            prefillRates()
            {
                if (this.schedule.id) {
                    // Use the schedule's rates if the caregiver_id matches the schedule's caregiver_id
                    if (this.schedule.caregiver_id == this.selectedCaregiver.id) {
                        this.form.caregiver_rate = this.schedule.caregiver_rate;
                        this.form.provider_fee = this.schedule.provider_fee
                        return;
                    }
                }

                this.form.caregiver_rate = this.selectedCaregiver.pivot[`caregiver_${this.rateType.toLowerCase()}_rate`];
                this.form.provider_fee = this.selectedCaregiver.pivot[`provider_${this.rateType.toLowerCase()}_fee`];
            }
        },

        watch: {
            model(val) {
                // Hide warning modal if hiding this modal
                if (!val) {
                    this.hideMaxHoursWarning();
                }

                // Update local modal bool
                this.scheduleModal = val;
            },

            selectedSchedule(val) {
                // Force back to first tab
                this.resetTabs();

                // Clear copied values
                this.copiedSchedule = {};

                // Re-create the form object
                this.makeForm();

                // Use cg all mode if an caregiver is pre-selected
                if (this.schedule.caregiver_id) {
                    this.cgMode = 'all';
                } else {
                    this.cgMode = 'client';
                }
            },

            scheduleModal(val) {
                this.createType = null;
                this.$emit('update:model', val);
                if (val) {
                    this.loadClientData();
                }
            },

            startTime(val) {
                this.form.duration = this.getDuration();
                if (this.form.daily_rates) {
                    // Lock end time to start time for daily rates
                    this.endTime = val;
                }
            },

            endTime() {
                this.form.duration = this.getDuration();
            },

            'form.daily_rates': function(val, old_val) {
                this.prefillRates();
                if (val) {
                    // Lock end time to start time for daily rates
                    this.endTime = this.startTime;
                }
            },

            'form.client_id': function(val, old_val) {
                this.loadCarePlans(val, old_val);
                this.loadAllyPctFromClient(val);
                this.loadCaregivers();
            },

            'form.caregiver_id': function(val, old_val) {
                this.prefillRates();
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
