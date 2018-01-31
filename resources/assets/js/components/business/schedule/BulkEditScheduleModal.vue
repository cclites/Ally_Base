<template>
    <b-modal title="Bulk Update Schedules" v-model="showModal" size="lg" class="modal-fit-more">
        <b-container fluid>
            <b-row>
                <b-col>
                    <b-card title="Match Against">
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
                                        <option value="">--All Clients--</option>
                                        <option v-for="client in clients" :value="client.id">{{ client.name }}</option>
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
                                        <option value="">--All Caregivers--</option>
                                        <option value="0">Unassigned</option>
                                        <option v-for="caregiver in caregivers" :value="caregiver.id">{{ caregiver.nameLastFirst }}</option>
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
                                    <label class="custom-control custom-checkbox" v-for="day in daysOfWeek">
                                        <input type="checkbox" class="custom-control-input" name="bydays[]" v-model="form.bydays" :value="day">
                                        <span class="custom-control-indicator"></span>
                                        <span class="custom-control-description">{{ day }}</span>
                                    </label>
                                </div>
                            </b-col>
                        </b-row>
                    </b-card>
                </b-col>
            </b-row>
            <b-row>
                <b-col>
                    <b-card title="Update Values">
                        <b-row>
                            <b-col>
                                <label class="custom-control custom-checkbox" style="margin: 5px 0 0 0;">
                                    <input type="checkbox" class="custom-control-input" v-model="lockTiming" :value="true">
                                    <span class="custom-control-indicator"></span>
                                    <span class="custom-control-description">No Shift Timing Change</span>
                                </label>
                                <b-row>
                                    <b-col>
                                        <b-form-group label="Start Time" label-for="new_start_time">
                                            <time-picker id="new_start_time"
                                                         v-model="form.new_start_time"
                                                         :readonly="lockTiming"
                                                         @click.native="unlockAndFocus('lockTiming', $event)"
                                            />
                                            <input-help :form="form" field="new_start_time" text=""/>
                                        </b-form-group>
                                    </b-col>
                                    <b-col>
                                        <b-form-group :label="endTimeLabel" label-for="new_end_time">
                                            <time-picker id="new_end_time"
                                                         v-model="new_end_time"
                                                         :readonly="lockTiming"
                                                         @click.native="unlockAndFocus('lockTiming', $event)"
                                            />
                                            <input-help :form="form" field="new_duration" text=""/>
                                        </b-form-group>
                                    </b-col>
                                </b-row>
                            </b-col>
                        </b-row>
                        <b-row>
                            <b-col>
                                <b-form-group label="Special Designation" label-for="new_hours_type">
                                    <label class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" v-model="lockHoursType" :value="true">
                                        <span class="custom-control-indicator"></span>
                                        <span class="custom-control-description">No change</span>
                                    </label>
                                    <b-form-select id="new_hours_type"
                                                   v-model="form.new_hours_type"
                                                   :readonly="lockHoursType"
                                                   @click.native="unlockAndFocus('lockHoursType', $event)"
                                    >
                                        <option value="default">Regular Shift</option>
                                        <option value="holiday">Holiday</option>
                                        <option value="overtime">Overtime</option>
                                    </b-form-select>
                                    <input-help :form="form" field="new_hours_type" text=""/>
                                </b-form-group>
                            </b-col>
                            <!-- HIDE OVERTIME DURATION FOR NOW -->
                            <b-col v-if="false">
                                <b-form-group label="Overtime Hours" label-for="new_provider_fee">
                                    <label class="custom-control custom-checkbox">
                                        <input type="checkbox"
                                               class="custom-control-input"
                                               v-model="lockOvertimeHours"
                                               :value="true">
                                        <span class="custom-control-indicator"></span>
                                        <span class="custom-control-description">No change</span>
                                    </label>
                                    <label class="custom-control custom-checkbox">
                                        <input type="checkbox"
                                               class="custom-control-input"
                                               v-model="entireShiftOvertime"
                                               :value="true">
                                        <span class="custom-control-indicator"></span>
                                        <span class="custom-control-description">Entire Shift</span>
                                    </label>
                                    <b-form-input type="number"
                                                  id="new_overtime_duration"
                                                  v-model="form.new_overtime_duration"
                                                  step="any"
                                                  :readonly="lockOvertimeHours || entireShiftOvertime"
                                                  @click.native="unlockOvertimeHours($event)"
                                    />
                                    <input-help :form="form" field="new_overtime_duration" text=""/>
                                </b-form-group>
                            </b-col>
                        </b-row>
                        <b-row>
                            <b-col>
                                <b-form-group label="Caregiver" label-for="new_caregiver_id">
                                    <b-form-select id="new_caregiver_id"
                                                   v-model="form.new_caregiver_id"
                                    >
                                        <option value="">No Change</option>
                                        <option value="0">Unassigned</option>
                                        <option v-for="caregiver in caregivers" :value="caregiver.id">{{ caregiver.nameLastFirst }}</option>
                                    </b-form-select>
                                    <input-help :form="form" field="new_caregiver_id" text=""/>
                                </b-form-group>
                            </b-col>
                        </b-row>
                        <b-row>
                            <b-col>
                                <b-form-group label="Caregiver Rate" label-for="new_caregiver_rate">
                                    <label class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" v-model="lockCaregiverRate" :value="true">
                                        <span class="custom-control-indicator"></span>
                                        <span class="custom-control-description">No change</span>
                                    </label>
                                    <b-form-input type="number"
                                                  id="new_caregiver_rate"
                                                  v-model="form.new_caregiver_rate"
                                                  step="any"
                                                  :readonly="lockCaregiverRate"
                                                  @click.native="unlockAndFocus('lockCaregiverRate', $event)"
                                    />
                                    <input-help :form="form" field="new_caregiver_rate" text=""/>
                                </b-form-group>
                            </b-col>
                            <b-col>
                                <b-form-group label="Provider Fee" label-for="new_provider_fee">
                                    <label class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" v-model="lockProviderFee" :value="true">
                                        <span class="custom-control-indicator"></span>
                                        <span class="custom-control-description">No change</span>
                                    </label>
                                    <b-form-input type="number"
                                                  id="new_provider_fee"
                                                  v-model="form.new_provider_fee"
                                                  step="any"
                                                  :readonly="lockProviderFee"
                                                  @click.native="unlockAndFocus('lockProviderFee', $event)"
                                    />
                                    <input-help :form="form" field="new_provider_fee" text=""/>
                                </b-form-group>
                            </b-col>
                        </b-row>
                        <b-row>
                            <b-col>
                                <b-form-group label="New Note" label-for="new_note_method">
                                    <b-form-select id="new_note_method"
                                                   v-model="form.new_note_method"
                                    >
                                        <option value="">No Change</option>
                                        <option value="append">Append Note</option>
                                        <option value="overwrite">Overwrite Existing</option>
                                    </b-form-select>
                                    <b-form-textarea
                                            id="notes"
                                            name="notes"
                                            :rows="3"
                                            v-model="form.new_note_text"
                                            :readonly="!form.new_note_method"
                                    >
                                    </b-form-textarea>
                                    <input-help :form="form" field="new_note_method" text=""/>
                                    <input-help :form="form" field="new_note_text" text=""/>
                                </b-form-group>
                            </b-col>
                        </b-row>
                    </b-card>
                </b-col>
            </b-row>
        </b-container>
        <div slot="modal-footer">
           <b-btn variant="default" @click="showModal=false">Close</b-btn>
            <b-btn variant="info" @click="save()" :disabled="submitting">
                <i class="fa fa-spinner fa-spin" v-show="submitting"></i>
                Update Schedules
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
            'caregiver': Object,
            'client': Object,
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
                form: new Form({
                    'start_date': moment().format('MM/DD/YYYY'),
                    'end_date': moment().format('MM/DD/YYYY'),
                    'start_time': '09:00 AM',
                    'hours_type': '',
                    'client_id': (this.client) ? this.client.id : '',
                    'caregiver_id': (this.caregiver) ? this.caregiver.id : '',
                    'bydays': [],

                    'new_start_time': '',
                    'new_duration': null,
                    'new_hours_type': '',
                    'new_overtime_duration': '',
                    'new_caregiver_id': '',
                    'new_caregiver_rate': '',
                    'new_provider_fee': '',
                    'new_note_method': '',
                    'new_note_text': '',
                }),
                submitting: false,
                disabled: {
                    caregiver_id: (this.caregiver) ? true : false,
                    client_id: (this.client) ? true : false,
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
                let message = 'Are you sure you wish to update all schedules between ' + this.form.start_date +
                    ' and ' + this.form.end_date + ' matching the given criteria?';

                if (this.form.end_date === '01/01/2100') {
                    message = 'Are you sure you wish to update all schedules after ' + this.form.start_date +
                            ' matching the given criteria?';
                }

                if (!confirm(message)) {
                    return;
                }

                let url = '/business/schedule/bulk_update';
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
            },

        },

        watch: {
            allFutureDates(val) {
                this.form.end_date = (val) ? '01/01/2100' : moment().format('MM/DD/YYYY');
            },

            anyStartTime(val) {
                this.form.start_time = null;
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
