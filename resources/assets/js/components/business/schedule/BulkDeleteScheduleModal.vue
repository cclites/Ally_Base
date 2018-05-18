<template>
    <b-modal title="Bulk Delete Schedules" v-model="showModal" size="lg" class="modal-fit-more">
        <b-container fluid>
            <b-row>
                <b-col>
                    <b-card title="Delete All Matching">
                        <b-row>
                            <b-col>
                                <b-form-group label="Start Date" label-for="date" style="margin-bottom: 0;">
                                    <date-picker v-model="form.start_date" />
                                    <input-help :form="form" field="start_date" text="" />
                                </b-form-group>
                            </b-col>
                            <b-col>
                                <b-form-group label="End Date" label-for="date" style="margin-bottom: 0;">
                                    <date-picker v-model="form.end_date"
                                                 :readonly="allFutureDates"
                                                 @click.native="unlockAndFocus('allFutureDates', $event)"
                                    />
                                    <input-help :form="form" field="end_date" text="" />
                                </b-form-group>
                                <label class="custom-control custom-checkbox" style="padding-right: 25px">
                                    <input type="checkbox" class="custom-control-input" v-model="allFutureDates" :value="true">
                                    <span class="custom-control-indicator"></span>
                                    <span class="custom-control-description">All Future Dates</span>
                                </label>
                            </b-col>
                        </b-row>
                        <b-row>
                            <b-col>
                                <b-form-group label="Start Time" label-for="start_time" style="margin-bottom: 0;">
                                    <time-picker v-model="form.start_time"
                                                 :readonly="anyStartTime"
                                                 :placeholder="anyStartTime ? 'Any' : ''"
                                                 @click.native="unlockAndFocus('anyStartTime', $event)"
                                    />
                                    <input-help :form="form" field="start_time" text=""/>
                                </b-form-group>
                                <label class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" v-model="anyStartTime" :value="true">
                                    <span class="custom-control-indicator"></span>
                                    <span class="custom-control-description">Any Starting Time</span>
                                </label>
                            </b-col>
                            <b-col>
                                <b-form-group label="Special Designation" label-for="hours_type">
                                    <b-form-select id="hours_type"
                                                   v-model="form.hours_type"
                                    >
                                        <option value="">--Any Designation--</option>
                                        <option value="default">Regular</option>
                                        <option value="overtime">Overtime</option>
                                        <option value="holiday">Holiday</option>
                                    </b-form-select>
                                    <input-help :form="form" field="hours_type" text=""/>
                                </b-form-group>
                            </b-col>
                        </b-row>
                        <b-row>
                            <b-col>
                                <b-form-group label="Client" label-for="client_id">
                                    <b-form-select id="client_id" 
                                                   v-model="form.client_id"
                                                   :disabled="disabled.client_id"
                                    >
                                        <option value="-">--Please Select--</option>
                                        <option value="">All Clients</option>
                                        <option v-for="client in clients" :value="client.id" :key="client.id">{{ client.name }}</option>
                                    </b-form-select>
                                    <input-help :form="form" field="client_id" text=""/>
                                </b-form-group>
                            </b-col>
                            <b-col>
                                <b-form-group label="Caregiver" label-for="caregiver_id">
                                    <b-form-select id="caregiver_id"
                                                   v-model="form.caregiver_id"
                                                   :disabled="disabled.caregiver_id"
                                    >
                                        <option value="-">--Please Select--</option>
                                        <option value="">All Caregivers</option>
                                        <option value="0">Unassigned</option>
                                        <option v-for="caregiver in caregivers" :value="caregiver.id" :key="caregiver.id">{{ caregiver.nameLastFirst }}</option>
                                    </b-form-select>
                                    <input-help :form="form" field="caregiver_id" text=""/>
                                </b-form-group>
                            </b-col>
                        </b-row>
                        <b-row>
                            <b-col>
                                <div class="form-check">
                                    <input-help :form="form" field="bydays" text="Select the days of the week to match against."/>
                                    <label class="custom-control custom-checkbox" style="padding-right: 25px">
                                        <input type="checkbox" class="custom-control-input" v-model="selectAllDays" :value="true">
                                        <span class="custom-control-indicator"></span>
                                        <span class="custom-control-description">All Days</span>
                                    </label>
                                    <label class="custom-control custom-checkbox" v-for="day in daysOfWeek" :key="day">
                                        <input type="checkbox" class="custom-control-input" name="bydays[]" v-model="form.bydays" :value="day">
                                        <span class="custom-control-indicator"></span>
                                        <span class="custom-control-description">{{ day }}</span>
                                    </label>
                                </div>
                            </b-col>
                        </b-row>
                        <b-row>
                            <b-col>
                                <div class="form-check">
                                    <input-help :form="form" field="daily_rates" text="Select the rate structure you wish to match against."/>
                                    <input name="daily_rates" v-model="form.daily_rates" type="radio" class="with-gap" id="delete_any_rates" value="">
                                    <label for="delete_any_rates" class="rate-label">Any Type of Rate</label>
                                    <input name="daily_rates" v-model="form.daily_rates" type="radio" class="with-gap" id="delete_hourly_rates" :value="0">
                                    <label for="delete_hourly_rates" class="rate-label">Hourly Rates</label>
                                    <input name="daily_rates" v-model="form.daily_rates" type="radio" class="with-gap" id="delete_daily_rates" :value="1">
                                    <label for="delete_daily_rates" class="rate-label">Daily Rates</label>
                                </div>
                            </b-col>
                        </b-row>
                    </b-card>
                </b-col>
            </b-row>
        </b-container>
        <div slot="modal-footer">
           <b-btn variant="default" @click="showModal=false">Close</b-btn>
            <b-btn variant="danger" @click="save()" :disabled="submitting">
                <i class="fa fa-spinner fa-spin" v-show="submitting"></i>
                Delete Schedules
            </b-btn>
        </div>
    </b-modal>
</template>

<script>
    import FormatsNumbers from "../../../mixins/FormatsNumbers";

    export default {
        mixins: [FormatsNumbers],

        props: {
            'value': Boolean,
            'selectedItem': Object,
            'items': Object,
            'caregiverId': {},
            'clientId': {},
        },

        data() {
            return {
                daysOfWeek: ['MO', 'TU', 'WE', 'TH', 'FR', 'SA', 'SU'],
                new_end_time: '',
                allFutureDates: false,
                selectAllDays: false,
                anyStartTime: false,
                lockTiming: true,
                lockHoursType: true,
                entireShiftOvertime: false,
                lockOvertimeHours: true,
                lockCaregiverRate: true,
                lockProviderFee: true,
                clients: [],
                caregivers: [],
                submitting: false,
                form: new Form(),
                disabled: {
                    caregiver_id: (this.caregiverId > 0),
                    client_id: (this.clientId > 0),
                },
            }
        },

        computed: {
            showModal: {
                get() {
                    return this.value;
                },
                set(value) {
                    this.$emit('input', value);
                }
            },
            endTimeLabel() {
                let label = 'End Time';
                if (this.form.new_duration) {
                    label = label + ' (' + this.numberFormat(this.form.new_duration / 60) + ' Hours)';
                }
                return label;
            }
        },

        mounted() {
            this.loadData();
        },

        methods: {
            makeForm() {
                // Reset all data values back to defaults
                this.new_end_time = '';
                this.allFutureDates = false;
                this.selectAllDays = false;
                this.anyStartTime = false;
                this.lockTiming = true;
                this.lockHoursType = true;
                this.entireShiftOvertime = false;
                this.lockOvertimeHours = true;
                this.lockCaregiverRate = true;
                this.lockProviderFee = true;

                // Recreate default form
                this.form = new Form({
                    'start_date': moment().format('MM/DD/YYYY'),
                    'end_date': moment().format('MM/DD/YYYY'),
                    'start_time': '09:00 AM',
                    'hours_type': '',
                    'client_id': (this.clientId > 0) ? this.clientId : '-',
                    'caregiver_id': (this.caregiverId > 0) ? this.caregiverId : '-',
                    'bydays': [],
                    'daily_rates': "",

                    //
                    // 'new_start_time': '',
                    // 'new_duration': null,
                    // 'new_hours_type': '',
                    // 'new_overtime_duration': '',
                    // 'new_caregiver_id': '',
                    // 'new_caregiver_rate': '',
                    // 'new_provider_fee': '',
                    // 'new_note_method': '',
                    // 'new_note_text': '',
                });
            },

            unlockAndFocus(lock, event) {
                console.log(lock, event);
                this[lock] = false;
                event.target.removeAttribute("readonly");
                event.target.focus();
            },

            unlockOvertimeHours(event) {
                this.entireShiftOvertime = false;
                this.lockOvertimeHours = false;
                event.target.removeAttribute("readonly");
                event.target.focus();
            },

            calculateDuration(end_time) {
                if (end_time && this.form.new_start_time) {
                    if (this.form.new_start_time == end_time) {
                        return 1440; // have 12:00am to 12:00am = 24 hours
                    }
                    let start = moment('2017-01-01 ' + this.form.new_start_time, 'YYYY-MM-DD h:mm A');
                    let end = moment('2017-01-01 ' + end_time, 'YYYY-MM-DD h:mm A');
                    console.log(start, end);
                    if (start && end) {
                        if (end.isBefore(start)) {
                            end = moment('2017-01-02 ' + end_time, 'YYYY-MM-DD h:mm A');
                        }
                        let diff = end.diff(start, 'minutes');
                        console.log(diff);
                        if (diff) {
                            return parseInt(diff);
                        }
                    }
                }
                return null;
            },

            save() {
                let message = 'Are you sure you wish to delete all schedules between ' + this.form.start_date +
                    ' and ' + this.form.end_date + ' matching the given criteria?';

                if (this.form.end_date === '01/01/2100') {
                    message = 'Are you sure you wish to delete all schedules after ' + this.form.start_date +
                            ' matching the given criteria?';
                }

                if (!confirm(message)) {
                    return;
                }

                let url = '/business/schedule/bulk_delete';
                this.submitting = true;
                this.form.post(url)
                    .then(response => {
                        this.$emit('refresh-events');
                        this.showModal = false;
                        this.submitting = false;
                    })
                    .catch(error => {
                        this.submitting = false;
                    });
            },

            loadData() {
                axios.get('/business/clients/list')
                    .then(response => {
                        this.clients = response.data;
                    });
                axios.get('/business/caregivers?json=1')
                    .then(response => {
                        this.caregivers = response.data;
                    });
            }
        },

        watch: {
            value(val) {
                if (val) this.makeForm();
            },

            allFutureDates(val) {
                this.form.end_date = (val) ? '01/01/2100' : moment().format('MM/DD/YYYY');
            },

            anyStartTime(val) {
                this.form.start_time = null;
            },

            caregiverId(val) {
                this.disabled.caregiver_id = (val > 0);
                this.form.caregiver_id = (val > 0) ? val : '-';
            },

            clientId(val) {
                this.disabled.client_id = (val > 0);
                this.form.client_id = (val > 0) ? val : '-';
            },

            entireShiftOvertime(val) {
                if (val) this.lockOvertimeHours = false;
                this.form.new_overtime_duration = (val) ? -1 : null; // -1 is designated to mean equals to duration
            },

            lockCaregiverRate(val) {
                this.form.new_caregiver_rate = null;
            },

            lockHoursType(val) {
                this.form.new_hours_type = null;
            },

            lockOvertimeHours(val) {
                if (val) this.entireShiftOvertime = false;
            },

            lockProviderFee(val) {
                this.form.new_provider_fee = null;
            },

            lockTiming(val) {
                this.form.new_start_time = null;
                this.new_end_time = null;
            },

            selectAllDays(val) {
                if (val) {
                    // If selected, set by days to every day of the week
                    this.form.bydays = this.daysOfWeek
                }
                else if (this.form.bydays == this.daysOfWeek) {
                    // If unselected AND bydays is still every day of the week, unset bydays
                    this.form.bydays = [];
                }
            },

            new_end_time(val) {
                this.form.new_duration = this.calculateDuration(val);
            },

            'form.bydays': function(val, old_val) {
                if (old_val == this.daysOfWeek) {
                    // If the previous value was every day of the week, uncheck All Days
                    this.selectAllDays = false;
                }
            }
        }
    }
</script>

<style>
    .rate-label {
        padding-right: 30px;
    }
    [type="radio"]:not(:checked) + label {
        font-size: 14px;
    }
</style>