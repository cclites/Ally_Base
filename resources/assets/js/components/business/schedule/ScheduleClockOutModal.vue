<template>
    <b-modal title="Clock Out Shift" v-model="showModal">
        <b-container fluid>
            <b-row>
                <div class="text-center mb-4">
                    This will clock out {{ caregiverName }}'s shift
                </div>

                <b-form-group label="Clocked In Date &amp; Time" label-for="checked_in_time">
                    <b-row>
                        <b-col cols="7">
                            <date-picker v-model="checked_in_date" placeholder="Date (MM/DD/YYYY)"></date-picker>
                        </b-col>
                        <b-col cols="5">
                            <time-picker v-model="checked_in_time" placeholder="Time (Ex. 12:00 PM)"></time-picker>
                        </b-col>
                    </b-row>
                    <input-help :form="form" field="checked_in_time" text="Confirm the date &amp; time the shift was clocked in to."></input-help>
                </b-form-group>

                <b-form-group label="Clocked Out Date &amp; Time" label-for="checked_out_time">
                    <b-row>
                        <b-col cols="7">
                            <date-picker v-model="checked_out_date" placeholder="Date (MM/DD/YYYY)"></date-picker>
                        </b-col>
                        <b-col cols="5">
                            <time-picker v-model="checked_out_time" placeholder="Time (Ex. 12:00 PM)"></time-picker>
                        </b-col>
                    </b-row>
                    <input-help :form="form" field="checked_out_time" text="Confirm the date &amp; time the shift was clocked out from."></input-help>
                </b-form-group>
            </b-row>
        </b-container>
        <div slot="modal-footer">
            <b-btn variant="info" @click="save()" :disabled="submitting">
                <i class="fa fa-spinner fa-spin" v-show="submitting"></i>
                Clock Out Shift
            </b-btn>
            <a :href="shiftUrl" class="btn btn-secondary">
                Edit Shift
            </a>
            <b-btn variant="default" @click="showModal=false">Cancel</b-btn>
        </div>
    </b-modal>
</template>

<script>
    export default {
        props: {
            'value': Boolean,
            'shift': {
                type: Object,
                default() {
                    return {};
                }
            },
        },

        data() {
            return {
                submitting: false,
                checked_in_time: '',
                checked_in_date: '',
                checked_out_time: '',
                checked_out_date: '',

                form: new Form({
                    checked_in_time: '',
                    checked_out_time: '',
                }),
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

            caregiverName() {
                if (this.shift && this.shift.caregiver) {
                    return this.shift.caregiver.name;
                }

                return '';
            },

            shiftUrl() {
                return this.shift.id ? `/business/shifts/${this.shift.id}` : '';
            }
        },

        methods: {
            setDefaultDateTimes() {
                this.checked_in_date = moment().format('MM/DD/YYYY');
                this.checked_out_date = moment().format('MM/DD/YYYY');
                this.checked_in_time = '09:00';
                this.checked_out_time = '10:00';
            },

            getClockedInMoment() {
                return moment(this.checked_in_date + ' ' + this.checked_in_time, 'MM/DD/YYYY HH:mm');
            },
            getClockedOutMoment() {
                return moment(this.checked_out_date + ' ' + this.checked_out_time, 'MM/DD/YYYY HH:mm');
            },

            save() {
                this.submitting = true;
                this.form.checked_in_time = this.getClockedInMoment().format();
                this.form.checked_out_time = this.getClockedOutMoment().format();

                let url = this.shiftUrl + '/clockout';
                this.form.post(url)
                    .then( ({ data }) => {
                        console.log(data);
                        this.refreshEvents();
                    })
                    .catch(e => {
                        console.log(e);
                    })
            },

            refreshEvents() {
                this.$emit('refresh');
                this.showModal = false;
            },

            validateTimeDifference(field) {
                this.$nextTick(function() {
                    let clockin = this.getClockedInMoment();
                    let clockout = this.getClockedOutMoment();
                    if (clockin.isValid() && clockout.isValid()) {
                        let diffInMinutes = clockout.diff(clockin, 'minutes');
                        console.log(diffInMinutes);
                        if (diffInMinutes < 0) {
                            this.form.addError(field, 'The clocked out time cannot be less than the clocked in time.');
                        }
                        else if (diffInMinutes > 600) {
                            this.form.addError(field, 'Warning: This shift change exceeds a duration of 10 hours.');
                        }
                        else {
                            this.form.clearError(field);
                        }
                    }
                    else {
                        console.log('Invalid time?');
                    }
                });
            },

            /**
             * Sets the checkin time to the shifts check in time and sets
             * the check out time to now.
             */
            initDates() {
                if (this.shift.checked_in_time) {
                    let checkin = moment.utc(this.shift.checked_in_time).local();
                    let checkout = (this.shift.checked_out_time) ? moment.utc(this.shift.checked_out_time).local() : moment().local();
                    this.checked_in_date = checkin.format('MM/DD/YYYY');
                    this.checked_in_time = checkin.format('HH:mm');
                    this.checked_out_date = (checkout) ? checkout.format('MM/DD/YYYY') : null;
                    this.checked_out_time = (checkout) ? checkout.format('HH:mm') : null;
                }
                else {
                    this.setDefaultDateTimes();
                }
            }
        },

        watch: {
            /**
             * Initialize dates when modal is shown.
             */
            value(val) {
                if (val) {
                    this.initDates();
                }
            },

            checked_in_date(val, old) {
                if (old) {
                    this.validateTimeDifference('checked_in_time');
                    if (!this.checked_out_date || this.checked_out_date < this.checked_in_date) {
                        this.checked_out_date = val;
                    }
                    else {
                        if (this.getClockedOutMoment().diff(this.getClockedInMoment(), 'hours') > 12) {
                            this.checked_out_date = val;
                        }
                    }
                }
            },

            checked_in_time(val, old) {
                if (old) this.validateTimeDifference('checked_in_time')
            },

            checked_out_date(val, old) {
                if (old) this.validateTimeDifference('checked_out_time')
            },

            checked_out_time(val, old) {
                if (old) this.validateTimeDifference('checked_out_time')
            },
        }
    }
</script>
