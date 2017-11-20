<template>
    <div>
        <div class="alert alert-warning" v-if="shift.id && !form.checked_out_time">
            <b>Warning!</b> This shift is currently clocked in.  To clock out this shift, set a Clocked Out Time and click "Save &amp; Verify".
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
                        <b-form-group label="Mileage" label-for="mileage">
                            <b-form-input
                                    id="mileage"
                                    name="mileage"
                                    type="number"
                                    v-model="form.mileage"
                                    step="1"
                            >
                            </b-form-input>
                            <input-help :form="form" field="mileage" text="Confirm the number of miles driven during this shift."></input-help>
                        </b-form-group>
                    </b-col>
                </b-row>
                <b-row>
                    <b-col md="4" sm="6">
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
                        <b-form-group label="Shift Designation" label-for="hours_type">
                            <b-form-select
                                    id="hours_type"
                                    name="hours_type"
                                    v-model="form.hours_type"
                            >
                                <option value="default">None - Regular Shift</option>
                                <option value="holiday">Holiday</option>
                                <option value="overtime">Overtime</option>
                            </b-form-select>
                            <input-help :form="form" field="" text=""></input-help>
                        </b-form-group>
                    </b-col>
                    <b-col md="8" sm="6">
                        <b-form-group label="Shift Notes / Caregiver Comments" label-for="caregiver_comments">
                            <b-textarea
                                    id="caregiver_comments"
                                    name="caregiver_comments"
                                    :rows="6"
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
                    <b-col lg="12" v-if="!shift.readOnly">
                        <span v-if="!deleted">
                            <b-button variant="success" type="submit">Save Shift</b-button>
                            <b-button variant="info" type="button" @click="saveAndVerify()" v-if="!form.verified">Save &amp; Verify</b-button>
                            <b-button variant="danger" type="button" @click="deleteShift()" v-if="shift.id"><i class="fa fa-times"></i> Delete Shift</b-button>
                        </span>
                        <b-button variant="primary" href="/business/reports/shifts"><i class="fa fa-backward"></i> Return to Shift History</b-button>
                    </b-col>
                    <b-col lg="12" v-else>
                        <b-button variant="info" disabled><i class="fa fa-lock"></i> This Shift is Locked For Modification</b-button>
                        <b-button variant="primary" href="/business/reports/shifts"><i class="fa fa-backward"></i> Return to Shift History</b-button>
                    </b-col>
                </b-row>
            </form>

            <business-issue-modal v-model="issueModal" :shift-id="shift.id" :selectedItem="selectedIssue" :items.sync="issues"></business-issue-modal>
        </b-card>
    </div>
</template>

<script>
    export default {
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
            'caregivers': Array,
            'clients': Array,
        },
        data() {
            return {
                form: new Form({
                    client_id: (this.shift.id) ? this.shift.client_id : null,
                    caregiver_id: (this.shift.id) ? this.shift.caregiver_id : null,
                    caregiver_comments: (this.shift.id) ? this.shift.caregiver_comments : null,
                    checked_in_time: (this.shift.id) ? this.shift.checked_in_time : null,
                    checked_out_time: (this.shift.id) ? this.shift.checked_out_time : null,
                    mileage: (this.shift.id) ? this.shift.mileage : 0,
                    other_expenses: (this.shift.id) ? this.shift.other_expenses : 0,
                    hours_type: (this.shift.hours_type) ? this.shift.hours_type : 'default',
                    verified: (this.shift.id) ? this.shift.verified : true,
                    caregiver_rate: (this.shift.id) ? this.shift.caregiver_rate : '',
                    provider_fee: (this.shift.id) ? this.shift.provider_fee : '',
                    activities: [],
                    issues: [], // only used for creating shifts, modifying a shift's issues is handled immediately in the modal
                }),
                checked_in_time: '',
                checked_in_date: '',
                checked_out_time: '',
                checked_out_date: '',
                issueModal: false,
                selectedIssue: null,
                deleted: false,
            }
        },
        mounted() {
            if (this.shift.id) {
                let checkin = moment.utc(this.shift.checked_in_time).local();
                let checkout = (this.shift.checked_out_time) ? moment.utc(this.shift.checked_out_time).local() : null;
                this.checked_in_date = checkin.format('MM/DD/YYYY');
                this.checked_in_time = checkin.format('h:mm A');
                this.checked_out_date = (checkout) ? checkout.format('MM/DD/YYYY') : null;
                this.checked_out_time = (checkout) ? checkout.format('h:mm A') : null;
                this.form.activities = this.getShiftActivityList();
            }
            else {
                this.checked_in_date = moment().format('MM/DD/YYYY');
                this.checked_out_date = moment().format('MM/DD/YYYY');
                this.checked_in_time = '09:00 AM';
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
                return moment(this.checked_in_date + ' ' + this.checked_in_time, 'MM/DD/YYYY h:mm A');
            },
            getClockedOutMoment() {
                return moment(this.checked_out_date + ' ' + this.checked_out_time, 'MM/DD/YYYY h:mm A');
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
            saveAndVerify() {
                this.form.verified = true;
                this.saveShift();
            },
            validateTimeDifference(field) {
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
            }
        },
        watch: {
            checked_in_date(val, old) {
                if (old) this.validateTimeDifference('checked_in_time')
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
        },
    }
</script>

<style>
    .deletedForm {
        opacity: 0.3;
    }
</style>