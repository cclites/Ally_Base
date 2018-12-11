<template>
    <form @submit.prevent="updateShift()" @keydown="form.clearError($event.target.name)">
        <b-modal :title="modalTitle" v-model="showModal" size="lg">
            <b-container fluid>
                <b-row>
                    <b-col md="6">
                        <!-- start_time -->
                        <b-form-group label="Clocked In" label-for="start_time" label-class="required">
                            <time-picker v-model="form.start_time" placeholder="HH:MM"></time-picker>
                            <input-help :form="form" field="start_time" text=""></input-help>
                        </b-form-group>
                    </b-col>
                    <b-col md="6">
                        <!-- end_time -->
                        <b-form-group label="Clocked Out" label-for="end_time" label-class="required">
                            <time-picker v-model="form.end_time" placeholder="HH:MM"></time-picker>
                            <input-help :form="form" field="end_time" text=""></input-help>
                        </b-form-group>
                    </b-col>

                    <b-col md="12">
                        <!-- activities -->
                            <b-form-group label="Activities Performed Out" label-for="" label-class="required">
                                <input-help :form="form" field="activities" text=""></input-help>
                                <div class="form-check">
                                    <b-row>
                                        <b-col md="6">
                                            <label class="custom-control custom-checkbox" v-for="activity in leftHalfActivities" :key="activity.id" style="clear: left; float: left;">
                                                <input type="checkbox" class="custom-control-input" v-model="form.activities" :value="activity.id">
                                                <span class="custom-control-indicator"></span>
                                                <span class="custom-control-description">{{ activity.code }} - {{ activity.name }}</span>
                                            </label>
                                        </b-col>
                                        <b-col md="6">
                                            <label class="custom-control custom-checkbox" v-for="activity in rightHalfActivities" :key="activity.id" style="clear: left; float: left;">
                                                <input type="checkbox" class="custom-control-input" v-model="form.activities" :value="activity.id">
                                                <span class="custom-control-indicator"></span>
                                                <span class="custom-control-description">{{ activity.code }} - {{ activity.name }}</span>
                                            </label>
                                        </b-col>
                                    </b-row>
                                </div>
                            </b-form-group>
                    </b-col>

                    <b-col md="6">
                        <b-form-group v-show="isOfficeUser" label="Caregiver Hourly Rate" label-for="caregiver_rate">
                            <b-form-input
                                    id="caregiver_rate"
                                    name="caregiver_rate"
                                    type="number"
                                    step="any"
                                    v-model="form.caregiver_rate"
                            >
                            </b-form-input>
                            <input-help :form="form" field="caregiver_rate" text=""></input-help>
                        </b-form-group>
                    </b-col>
                    <b-col md="6">
                        <b-form-group v-show="isOfficeUser" label="Provider Hourly Fee" label-for="provider_fee">
                            <b-form-input
                                    id="provider_fee"
                                    name="provider_fee"
                                    type="number"
                                    step="any"
                                    v-model="form.provider_fee"
                            >
                            </b-form-input>
                            <input-help :form="form" field="provider_fee" text=""></input-help>
                        </b-form-group>
                    </b-col>

                    <b-col md="6">
                        <!-- mileage -->
                        <b-form-group label="Mileage" label-for="mileage">
                            <b-form-input
                                    id="mileage"
                                    name="mileage"
                                    type="number"
                                    v-model="form.mileage"
                                    step="any"
                                    min="0"
                                    max="1000"
                            />
                            <input-help :form="form" field="mileage" text=""></input-help>
                        </b-form-group>
                    </b-col>

                    <b-col md="6">
                        <!-- other_expenses -->
                        <b-form-group label="Other Expenses" label-for="other_expenses">
                            <b-form-input
                                    id="other_expenses"
                                    name="other_expenses"
                                    type="number"
                                    v-model="form.other_expenses"
                                    step="any"
                                    min="0"
                                    max="1000"
                            />
                            <input-help :form="form" field="other_expenses" text=""></input-help>
                        </b-form-group>
                    </b-col>

                    <b-col md="12">
                        <b-form-group label="Notes" label-for="caregiver_comments">
                            <b-textarea
                                    id="caregiver_comments"
                                    name="caregiver_comments"
                                    :rows="4"
                                    v-model="form.caregiver_comments"
                            ></b-textarea>
                            <input-help :form="form" field="caregiver_comments" text=""></input-help>
                        </b-form-group>
                    </b-col>
                </b-row>
            </b-container>

            <div slot="modal-footer">
                <b-button variant="info" type="submit">Save</b-button>
                <b-btn variant="default" @click="showModal = false">Close</b-btn>
            </div>
        </b-modal>
    </form>
</template>

<script>
    export default {
        props: {
            value: {},
            entry: { type: Object, default: {} },
            activities: { type: Array, default: [] },
            isOfficeUser: { type: Boolean, default: false },
        },

        data: () => ({
            form: new Form({}),
        }),

        computed: {
            showModal: {
                get() {
                    return this.value;
                },
                set(value) {
                    this.$emit('input', value);
                }
            },

            modalTitle() {
                if (! this.form.date) {
                    return '';
                }

                return this.dow(this.form.date, true) + ' ' + moment(this.form.date).format('M/D/YYYY');
            },

            defaultRate() {
                return this.entry.client.caregiver_hourly_rate || 0;
            },

            defaultFee() {
                return this.entry.client.provider_hourly_fee || 0;
            },

            leftHalfActivities() {
                return this.getHalfOfActivities(true);
            },

            rightHalfActivities() {
                return this.getHalfOfActivities(false);
            },
        },

        mounted() {
        },

        methods: {
            updateShift() {
                this.form.clearError();
                if (!this.isValidShift(this.form)) {
                    return;
                }

                // convert date and start/end time to proper check in/out datetime
                this.form.checked_in_time = moment(this.form.date + ' ' + this.form.start_time, 'MM/DD/YYYY HH:mm').utc().format('YYYY-MM-DD HH:mm:ss');

                let start = moment(this.form.start_time, 'HH:mm');
                let end = moment(this.form.end_time, 'HH:mm');

                let endDate = moment(this.form.date + ' ' + this.form.end_time, 'MM/DD/YYYY HH:mm');
                if (end.isBefore(start)) {
                    // overlaps to the next day
                    endDate = endDate.add(1, 'day');
                }
                this.form.checked_out_time = endDate.utc().format('YYYY-MM-DD HH:mm:ss');

                this.$emit('updated', this.form.data());
                this.showModal = false;
            },

            isValidShift(data) {
                if (!this.validDate(data.start_time)) {
                    this.form.addError('start_time', 'Clock in time is required');
                }
                
                if (!this.validDate(data.end_time)) {
                    this.form.addError('end_time', 'Clock out time is required');
                }

                if (data.mileage !== '' && isNaN(data.mileage)) {
                    this.form.addError('mileage', 'Invalid entry');
                }

                if (data.other_expenses !== '' && isNaN(data.other_expenses)) {
                    this.form.addError('other_expenses', 'Invalid entry');
                }

                if (! data.activities || data.activities.length == 0) {
                    this.form.addError('activities', 'You must select at least one activity');
                }
                
                if (this.isOfficeUser) {
                    if (isNaN(data.caregiver_rate)) {
                        this.form.addError('caregiver_rate', 'Invalid');
                    }

                    if (isNaN(data.provider_fee)) {
                        this.form.addError('provider_fee', 'Invalid');
                    }
                }
                
                return !this.form.hasError();
            },

            validDate(val) {
                if (!val || val == '') return false;
                return moment(val, 'mm/dd/yyyy').isValid();
            },
            
            validTime() {
                if (!val || val == '') return false;
                return moment(this.value, 'hh:mm').isValid();
            },

            dow(date, full = false) {
                return moment(date).format(full ? 'dddd' : 'ddd');
            },

            getHalfOfActivities(leftHalf = true)
            {
                let half_length = Math.ceil(this.activities.length / 2);
                let clone = this.activities.slice(0);
                let left = clone.splice(0,half_length);
                return (leftHalf) ? left : clone;
            },
        },

        watch: {
            entry(val) {
                if (val) {
                    // convert check in/out dates to entry date start/end time
                    let checkin = moment.utc(this.entry.checked_in_time).local();
                    let checkout = moment.utc(this.entry.checked_in_time).add(1, 'hour').local();
                    if (this.entry.checked_out_time) {
                        checkout = moment.utc(this.entry.checked_out_time).local();
                    }
                    let data = {
                        date: checkin.format('MM/DD/YYYY'),
                        start_time: checkin.format('HH:mm'),
                        end_time: checkout.format('HH:mm'),
                    }

                    this.form = new Form({
                        ...data,
                        ...this.entry
                    });
                }
            },
        },

    }
</script>
