<template>
    <div>
        <div class="alert alert-warning" v-if="shift.id && !form.checked_out_time">
            <b>Warning!</b> This shift is currently clocked in.  To clock out this shift, set a Clocked Out Time and click "Save".
        </div>
        <div class="alert alert-warning" v-if="status === 'WAITING_FOR_CONFIRMATION'">
            <b>Warning!</b> This shift is unconfirmed.  Confirm the details and click "Save &amp; Confirm".
        </div>
        <b-card
                :header="title"
                header-text-variant="white"
                header-bg-variant="info"
        >
            <form @submit.prevent="saveShift()" @keydown="form.clearError($event.target.name)" :class="formClass">
                <b-row>
                    <b-col lg="6">
                        <b-form-group label="Client" label-for="client_id">
                            <b-form-select
                                    id="client_id"
                                    name="client_id"
                                    v-model="form.client_id"
                            >
                                <option v-for="item in clients" :value="item.id">{{ item.nameLastFirst }}</option>
                            </b-form-select>
                            <input-help :form="form" field="client_id" text=""></input-help>
                        </b-form-group>
                    </b-col>
                    <b-col lg="6">
                        <b-form-group label="Caregiver" label-for="caregiver_id">
                            <b-form-select
                                    id="caregiver_id"
                                    name="caregiver_id"
                                    v-model="form.caregiver_id"
                            >
                                <option v-for="item in caregivers" :value="item.id">{{ item.nameLastFirst }}</option>
                            </b-form-select>
                            <input-help :form="form" field="caregiver_id" text=""></input-help>
                        </b-form-group>
                    </b-col>
                </b-row>
                <b-row>
                    <b-col lg="6">
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
                    </b-col>
                    <b-col lg="6">
                        <b-form-group label="Mileage" label-for="mileage">
                            <b-form-input
                                    id="mileage"
                                    name="mileage"
                                    type="number"
                                    v-model="form.mileage"
                                    step="any"
                            >
                            </b-form-input>
                            <input-help :form="form" field="mileage" text="Confirm the number of miles driven during this shift."></input-help>
                        </b-form-group>
                        <b-form-group label="Other Expenses" label-for="other_expenses">
                            <b-form-input
                                    id="other_expenses"
                                    name="other_expenses"
                                    type="number"
                                    v-model="form.other_expenses"
                                    step="any"
                            >
                            </b-form-input>
                            <input-help :form="form" field="other_expenses" text="Confirm the dollar amount of other expenses on this shift."></input-help>
                        </b-form-group>
                    </b-col>
                </b-row>
                <b-row>
                    <b-col md="5" sm="6">
                        <b-row>
                            <b-col sm="6">
                                <b-form-group label="Caregiver Hourly Rate" label-for="caregiver_rate">
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
                            <b-col sm="6">
                                <b-form-group label="Provider Hourly Fee" label-for="provider_fee">
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
                        </b-row>
                        <b-row>
                            <b-col sm="6">
                                <b-form-group :label="'Processing Fee (' + percentageFormat(allyPct) + ')'">
                                    <b-form-input
                                            :value="allyFee"
                                            readonly
                                    >
                                    </b-form-input>
                                </b-form-group>
                            </b-col>
                            <b-col sm="6">
                                <b-form-group label="Total Hourly Rate">
                                    <b-form-input
                                            :value="totalRate"
                                            readonly
                                    >
                                    </b-form-input>
                                </b-form-group>
                            </b-col>
                        </b-row>
                        <b-form-group label="Shift Designation" label-for="hours_type">
                            <b-form-select
                                    id="hours_type"
                                    name="hours_type"
                                    v-model="form.hours_type"
                            >
                                <option value="default">Regular Shift</option>
                                <option value="holiday">Holiday</option>
                                <option value="overtime">Overtime</option>
                            </b-form-select>
                            <input-help :form="form" field="" text=""></input-help>
                        </b-form-group>
                    </b-col>
                    <b-col md="7" sm="6">
                        <b-form-group label="Other Expenses Description" label-for="other_expenses_desc">
                            <b-textarea
                                    id="other_expenses_desc"
                                    name="other_expenses_desc"
                                    :rows="2"
                                    v-model="form.other_expenses_desc"
                            >
                            </b-textarea>
                            <input-help :form="form" field="other_expenses_desc" text=""></input-help>
                        </b-form-group>
                        <b-form-group label="Shift Notes / Caregiver Comments" label-for="caregiver_comments">
                            <b-textarea
                                    id="caregiver_comments"
                                    name="caregiver_comments"
                                    :rows="5"
                                    v-model="form.caregiver_comments"
                            >
                            </b-textarea>
                            <input-help :form="form" field="caregiver_comments" text=""></input-help>
                        </b-form-group>
                    </b-col>
                </b-row>
                <b-row>
                    <b-col lg="12">
                        <h5>Activities Performed</h5>
                        <b-row>
                            <b-col cols="12" md="6">
                                <div class="form-check">
                                    <label class="custom-control custom-checkbox" v-for="activity in leftHalfActivities" style="clear: left; float: left;">
                                        <input type="checkbox" class="custom-control-input" v-model="form.activities" :value="activity.id">
                                        <span class="custom-control-indicator"></span>
                                        <span class="custom-control-description">{{ activity.code }} - {{ activity.name }}</span>
                                    </label>
                                </div>
                            </b-col>
                            <b-col cols="12" md="6">
                                <div class="form-check">
                                    <label class="custom-control custom-checkbox" v-for="activity in rightHalfActivities" style="clear: left; float: left;">
                                        <input type="checkbox" class="custom-control-input" v-model="form.activities" :value="activity.id">
                                        <span class="custom-control-indicator"></span>
                                        <span class="custom-control-description">{{ activity.code }} - {{ activity.name }}</span>
                                    </label>
                                </div>
                            </b-col>
                        </b-row>
                    </b-col>
                </b-row>
                <b-row class="with-padding-top">
                    <b-col lg="12">
                        <h5>
                            Shift Issues
                            <b-btn size="sm" variant="info" @click="createIssue()" v-if="!deleted">Add an Issue</b-btn>
                        </h5>
                        <div class="table-responsive" v-if="issues.length">
                            <table class="table table-bordered">
                                <thead>
                                <tr>
                                    <th>Caregiver Injury</th>
                                    <th>Client Injury</th>
                                    <th>Comments</th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="issue in issues">
                                    <td>{{ issue.caregiver_injury ? 'Yes' : 'No' }}</td>
                                    <td>{{ issue.client_injury ? 'Yes' : 'No' }}</td>
                                    <td>{{ issue.comments }}</td>
                                    <td><b-btn size="sm" @click="editIssue(issue)">Edit</b-btn></td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="with-padding-bottom" v-else>
                            No recorded issues on this shift.
                        </div>
                    </b-col>
                </b-row>
                <b-row v-if="shift.id">
                    <b-col sm="6">
                        <table class="table">
                            <thead>
                            <tr>
                                <th colspan="2">Clock In EVV</th>
                            </tr>
                            </thead>
                            <tbody v-if="shift.checked_in_latitude || shift.checked_in_longitude">
                            <!-- <tr>
                                <th>Geocode</th>
                                <td>{{ shift.checked_in_latitude.slice(0,8) }},<br />{{ shift.checked_in_longitude.slice(0,8) }}</td>
                            </tr> -->
                            <tr>
                                <th>Distance</th>
                                <td>{{ in_distance }}m</td>
                            </tr>
                            </tbody>
                            <tbody v-else-if="shift.checked_in_number">
                            <tr>
                                <th>Phone Number</th>
                                <td>{{ shift.checked_in_number }}</td>
                            </tr>
                            </tbody>
                            <tbody v-else>
                            <tr>
                                <td colspan="2">No EVV data</td>
                            </tr>
                            </tbody>
                        </table>
                    </b-col>
                    <b-col sm="6">
                        <table class="table">
                            <thead>
                            <tr>
                                <th colspan="2">Clock Out EVV</th>
                            </tr>
                            </thead>
                            <tbody v-if="shift.checked_out_latitude || shift.checked_out_longitude">
                            <!-- <tr>
                                <th>Geocode</th>
                                <td>{{ shift.checked_out_latitude.slice(0,8) }},<br />{{ shift.checked_out_longitude.slice(0,8) }}</td>
                            </tr> -->
                            <tr>
                                <th>Distance</th>
                                <td>{{ out_distance }}m</td>
                            </tr>
                            </tbody>
                            <tbody v-else-if="shift.checked_out_number">
                            <tr>
                                <th>Phone Number</th>
                                <td>{{ shift.checked_out_number }}</td>
                            </tr>
                            </tbody>
                            <tbody v-else>
                            <tr>
                                <td colspan="2">No EVV data</td>
                            </tr>
                            </tbody>
                        </table>
                    </b-col>
                </b-row>
                <b-row>
                    <b-col lg="6" v-if="!shift.readOnly">
                        <span v-if="!deleted">
                            <b-button variant="success" type="button" @click="saveAndConfirm()" v-if="status === 'WAITING_FOR_CONFIRMATION'">Save &amp; Confirm</b-button>
                            <b-button variant="success" type="submit" v-else>Save Shift</b-button>
                            <b-button variant="primary" type="button" :href="'/business/shifts/' + shift.id + '/duplicate'" v-if="shift.id"><i class="fa fa-copy"></i> Duplicate to a New Shift</b-button>
                            <b-button variant="danger" type="button" @click="unconfirm()" v-if="status !== 'WAITING_FOR_CONFIRMATION'">Unconfirm</b-button>
                            <b-button variant="danger" type="button" @click="deleteShift()" v-if="shift.id"><i class="fa fa-times"></i> Delete Shift</b-button>
                        </span>
                        <b-button variant="secondary" href="/business/reports/shifts"><i class="fa fa-backward"></i> Return to Shift History</b-button>
                    </b-col>
                    <b-col lg="6" v-else>
                        <b-button variant="info" disabled><i class="fa fa-lock"></i> This Shift is Locked For Modification</b-button>
                        <b-button variant="success" @click="adminOverride()" v-if="admin">Admin Override: Save Anyways</b-button>
                        <b-button variant="primary" type="button" :href="'/business/shifts/' + shift.id + '/duplicate'" v-if="shift.id"><i class="fa fa-copy"></i> Duplicate to a New Shift</b-button>
                        <b-button variant="secondary" href="/business/reports/shifts"><i class="fa fa-backward"></i> Return to Shift History</b-button>
                    </b-col>
                    <b-col lg="6">
                        <b-row><span><strong>Added:</strong>&nbsp;{{ formatDateTimeFromUTC(shift.created_at) }}</span></b-row>
                        <b-row>
                            <span v-if="shift.confirmed_at"><strong>Confirmed:</strong>&nbsp;{{ formatDateTimeFromUTC(shift.confirmed_at) }}</span>
                            <span v-else><strong>Not Confirmed</strong></span>
                        </b-row>
                        <b-row>
                            <span v-if="shift.charged_at"><strong>Charged:</strong>&nbsp;{{ formatDateTimeFromUTC(shift.charged_at) }}</span>
                            <span v-else><strong>Not Charged</strong></span>
                        </b-row>
                    </b-col>
                </b-row>
            </form>

            <business-issue-modal v-model="issueModal" :shift-id="shift.id" :selectedItem="selectedIssue" :items.sync="issues"></business-issue-modal>
        </b-card>
    </div>
</template>

<script>
    import FormatsNumbers from '../mixins/FormatsNumbers'
    import FormatsDates from "../mixins/FormatsDates";

    export default {
        mixins: [FormatsNumbers, FormatsDates],

        props: {
            'shift': {
                default() {
                    return {};
                }
            },
            'caregiver': {},
            'client': {},
            'in_distance': {},
            'out_distance': {},
            'activities': Array,
            'issues': {
                default() {
                    return [];
                }
            },
            'admin': Number,
        },
        data() {
            return {
                form: new Form({
                    client_id: ('client_id' in this.shift) ? this.shift.client_id : null,
                    caregiver_id: ('caregiver_id' in this.shift) ? this.shift.caregiver_id : null,
                    caregiver_comments: ('caregiver_comments' in this.shift) ? this.shift.caregiver_comments : null,
                    checked_in_time: ('checked_in_time' in this.shift) ? this.shift.checked_in_time : null,
                    checked_out_time: ('checked_out_time' in this.shift) ? this.shift.checked_out_time : null,
                    mileage: ('mileage' in this.shift) ? this.shift.mileage : 0,
                    other_expenses: ('other_expenses' in this.shift) ? this.shift.other_expenses : 0,
                    other_expenses_desc: ('other_expenses_desc' in this.shift) ? this.shift.other_expenses_desc : null,
                    hours_type: ('hours_type' in this.shift) ? this.shift.hours_type : 'default',
                    verified: ('verified' in this.shift) ? this.shift.verified : true,
                    caregiver_rate: ('caregiver_rate' in this.shift) ? this.shift.caregiver_rate : '',
                    provider_fee: ('provider_fee' in this.shift) ? this.shift.provider_fee : '',
                    activities: [],
                    issues: [], // only used for creating shifts, modifying a shift's issues is handled immediately in the modal
                    override: false,
                }),
                status: (this.shift) ? this.shift.status : null,
                checked_in_time: '',
                checked_in_date: '',
                checked_out_time: '',
                checked_out_date: '',
                issueModal: false,
                selectedIssue: null,
                deleted: false,
                clients: [],
                caregivers: [],
                allyPct: 0.05,
                paymentType: 'NONE',
            }
        },
        mounted() {
            this.loadClientCaregiverData();
            this.loadAllyPctFromClient();
            // Do not check against id below to allow for shift duplication
            if (this.shift.checked_in_time) {
                let checkin = moment.utc(this.shift.checked_in_time).local();
                let checkout = (this.shift.checked_out_time) ? moment.utc(this.shift.checked_out_time).local() : null;
                this.checked_in_date = checkin.format('MM/DD/YYYY');
                this.checked_in_time = checkin.format('HH:mm');
                this.checked_out_date = (checkout) ? checkout.format('MM/DD/YYYY') : null;
                this.checked_out_time = (checkout) ? checkout.format('HH:mm') : null;
                this.form.activities = this.getShiftActivityList();
            }
            else {
                this.checked_in_date = moment().format('MM/DD/YYYY');
                this.checked_out_date = moment().format('MM/DD/YYYY');
                this.checked_in_time = '09:00 AM';
                this.checked_out_time = '10:00 AM';
            }
        },
        computed: {
            title() {
                return (this.shift.id) ? 'Shift Details' : 'Create a Manual Shift';
            },
            leftHalfActivities() {
                return this.getHalfOfActivities(true);
            },
            rightHalfActivities() {
                return this.getHalfOfActivities(false);
            },
            formClass() {
                if (this.deleted) return 'deletedForm';
                return '';
            },
            totalRate() {
                if (this.allyFee === null) return null;
                let caregiverHourlyFloat = parseFloat(this.form.caregiver_rate);
                let providerHourlyFloat = parseFloat(this.form.provider_fee);
                let totalRate = caregiverHourlyFloat + providerHourlyFloat + parseFloat(this.allyFee);
                return totalRate.toFixed(2);
            },
            allyFee() {
                if (!parseFloat(this.form.caregiver_rate)) return null;
                let caregiverHourlyFloat = parseFloat(this.form.caregiver_rate);
                let providerHourlyFloat = parseFloat(this.form.provider_fee);
                let allyFee = (caregiverHourlyFloat + providerHourlyFloat) * parseFloat(this.allyPct);
                return allyFee.toFixed(2);
            }
        },
        methods: {
            createIssue() {
                this.selectedIssue = null;
                this.issueModal = true;
            },
            editIssue(issue) {
                this.selectedIssue = issue;
                this.issueModal = true;
            },
            getClockedInMoment() {
                return moment(this.checked_in_date + ' ' + this.checked_in_time, 'MM/DD/YYYY HH:mm');
            },
            getClockedOutMoment() {
                return moment(this.checked_out_date + ' ' + this.checked_out_time, 'MM/DD/YYYY HH:mm');
            },
            getHalfOfActivities(leftHalf = true)
            {
                let half_length = Math.ceil(this.activities.length / 2);
                let clone = this.activities.slice(0);
                let left = clone.splice(0,half_length);
                return (leftHalf) ? left : clone;
            },
            getShiftActivityList() {
                let list = [];
                for (let activity of this.shift.activities) {
                    list.push(activity.id);
                }
                return list;
            },
            deleteShift() {
                if (confirm('Are you sure you wish to delete this shift?')) {
                    let form = new Form();
                    form.submit('delete', '/business/shifts/' + this.shift.id)
                        .then(response => this.deleted = true);
                }
            },
            saveShift() {
                this.form.checked_in_time = this.getClockedInMoment().format();
                this.form.checked_out_time = this.getClockedOutMoment().format();
                if (this.shift.id) {
                    this.form.patch('/business/shifts/' + this.shift.id);
                }
                else {
                    // Create a shift
                    this.form.issues = this.issues;
                    this.form.post('/business/shifts');
                }
            },
            adminOverride() {
                this.form.override = 1;
                return this.saveShift();
            },
            saveAndConfirm() {
                this.saveShift();
                if (this.shift.id) {
                    let form = new Form();
                    form.post('/business/shifts/' + this.shift.id + '/confirm')
                        .then(response => {
                            this.status = response.data.data.status;
                        });
                }
            },
            unconfirm() {
                if (this.shift.id) {
                    let form = new Form();
                    form.post('/business/shifts/' + this.shift.id + '/unconfirm')
                        .then(response => {
                            this.status = response.data.data.status;
                        });
                }
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
            loadClientCaregiverData() {
                axios.get('/business/clients').then(response => this.clients = response.data);
                axios.get('/business/caregivers').then(response => this.caregivers = response.data);
            },
            loadCaregiverRates() {
                if (!this.form.caregiver_id || !this.form.client_id) return;
                axios.get('/business/clients/' + this.form.client_id + '/caregivers/' + this.form.caregiver_id).then(response => {
                    console.log(response.data.pivot);
                    if (response.data.pivot) {
                        this.form.caregiver_rate = response.data.pivot.caregiver_hourly_rate;
                        this.form.provider_fee = response.data.pivot.provider_hourly_fee;
                    }
                });
            },
            loadAllyPctFromClient() {
                if (!this.form.client_id) return;
                axios.get('/business/clients/' + this.form.client_id + '/payment_type').then(response => {
                    this.allyPct = response.data.percentage_fee;
                    this.paymentType = response.data.payment_type;
                });
            },
        },
        watch: {
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
            'form.client_id': function() {
                if (!this.shift.id) {
                    this.loadCaregiverRates();
                }
                this.loadAllyPctFromClient();
            },
            'form.caregiver_id': function() {
                if (!this.shift.id) {
                    this.loadCaregiverRates();
                }
            }
        },
    }
</script>

<style>
    .deletedForm {
        opacity: 0.3;
    }
</style>
