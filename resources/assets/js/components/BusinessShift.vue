<template>
    <div>
        <div class="alert alert-warning" v-if="shift.id && !form.checked_out_time">
            <b>Warning!</b> This shift is currently clocked in.  To clock out this shift, set a Clocked Out Time and click "Save".
        </div>
        <div class="alert alert-warning" v-if="status === 'WAITING_FOR_CONFIRMATION'">
            <b>Warning!</b> This shift is unconfirmed.  Confirm the details and click "Save &amp; Confirm".
        </div>
        <form @submit.prevent="saveShift()" @keydown="form.clearError($event.target.name)" :class="formClass">
            <b-row>
                <b-col lg="6">
                    <b-form-group label="Client" label-for="client_id">
                        <b-form-select
                                id="client_id"
                                name="client_id"
                                v-model="form.client_id"
                        >
                            <option v-for="item in clients" :value="item.id" :key="item.id">{{ item.nameLastFirst }}</option>
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
                            <option v-for="item in caregivers" :value="item.id" :key="item.id">{{ item.nameLastFirst }}</option>
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
                    <b-form-group v-if="businessSettings().co_mileage" label="Mileage" label-for="mileage">
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
                    <b-form-group v-if="businessSettings().co_expenses" label="Other Expenses" label-for="other_expenses">
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
                            <b-form-group label="Shift Type (Rate)" label-for="daily_rates">
                                <b-form-select
                                        id="daily_rates"
                                        name="daily_rates"
                                        v-model="form.daily_rates"
                                        @change="changedDailyRates()"
                                >
                                    <option :value="0">Hourly Shift</option>
                                    <option :value="1">Daily Shift</option>
                                </b-form-select>
                                <input-help :form="form" field="daily_rates" text=""></input-help>
                            </b-form-group>
                        </b-col>
                        <b-col sm="6">
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
                                <input-help :form="form" field="hours_type" text=""></input-help>
                            </b-form-group>
                        </b-col>
                    </b-row>
                    <b-row>
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
                                <input-help :form="form" field="caregiver_rate" text=""></input-help>
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
                            <b-form-group :label="`Total ${rateType} Rate`">
                                <b-form-input
                                        :value="totalRate"
                                        readonly
                                >
                                </b-form-input>
                            </b-form-group>
                        </b-col>
                    </b-row>
                </b-col>
                <b-col md="7" sm="6">
                    <b-form-group v-if="businessSettings().co_expenses" label="Other Expenses Description" label-for="other_expenses_desc">
                        <b-textarea
                                id="other_expenses_desc"
                                name="other_expenses_desc"
                                :rows="2"
                                v-model="form.other_expenses_desc"
                        >
                        </b-textarea>
                        <input-help :form="form" field="other_expenses_desc" text=""></input-help>
                    </b-form-group>
                    <b-form-group v-if="businessSettings().co_comments" label="Shift Notes / Caregiver Comments" label-for="caregiver_comments">
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
                                <label class="custom-control custom-checkbox" v-for="activity in leftHalfActivities" :key="activity.id" style="clear: left; float: left;">
                                    <input type="checkbox" class="custom-control-input" v-model="form.activities" :value="activity.id">
                                    <span class="custom-control-indicator"></span>
                                    <span class="custom-control-description">{{ activity.code }} - {{ activity.name }}</span>
                                </label>
                            </div>
                        </b-col>
                        <b-col cols="12" md="6">
                            <div class="form-check">
                                <label class="custom-control custom-checkbox" v-for="activity in rightHalfActivities" :key="activity.id" style="clear: left; float: left;">
                                    <input type="checkbox" class="custom-control-input" v-model="form.activities" :value="activity.id">
                                    <span class="custom-control-indicator"></span>
                                    <span class="custom-control-description">{{ activity.code }} - {{ activity.name }}</span>
                                </label>
                            </div>
                        </b-col>
                    </b-row>
                </b-col>
            </b-row>
            <b-row v-if="shift.questions && shift.questions.length" class="with-padding-top">
                <b-col lg="12">
                    <b-form-group v-for="q in shift.questions"
                        :key="q.id"
                        :label="q.question + (q.required ? ' *' : '')">
                        <textarea v-model="form.questions[q.id]" class="form-control" rows="3" wrap="soft"></textarea>
                        <input-help :form="form" :field="`questions.${q.id}`"></input-help>
                    </b-form-group>
                </b-col>
            </b-row>
            <b-row v-if="shift.client && shift.client.goals.length" class="with-padding-top">
                <b-col lg="12">
                    <h4>Goals:</h4>
                    <b-form-group v-for="goal in shift.client.goals"
                        :key="goal.id"
                        :label="goal.question">
                        <!-- for some reason b-form-textarea had issues syncing with the dynamic goals object -->
                        <textarea v-model="form.goals[goal.id]" class="form-control" rows="3" wrap="soft"></textarea>
                    </b-form-group>
                </b-col>
            </b-row>
            <b-row class="with-padding-top" v-if="(businessSettings().co_issues || businessSettings().co_injuries) && !is_modal">
                <b-col lg="12">
                    <h5>
                        Shift Issues
                        <b-btn size="sm" variant="info" @click="createIssue()" v-if="!deleted">Add an Issue</b-btn>
                    </h5>
                    <div class="table-responsive" v-if="form.issues.length">
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
                            <tr v-for="(issue, index) in form.issues" :key="issue.id">
                                <td>
                                    <label class="custom-control custom-checkbox" style="clear: left; float: left;">
                                        <input type="checkbox" class="custom-control-input" v-model="form.issues[index].caregiver_injury">
                                        <span class="custom-control-indicator"></span>
                                        <span class="custom-control-description">Yes</span>
                                    </label>
                                </td>
                                <td>
                                    <label class="custom-control custom-checkbox" style="clear: left; float: left;">
                                        <input type="checkbox" class="custom-control-input" v-model="form.issues[index].client_injury">
                                        <span class="custom-control-indicator"></span>
                                        <span class="custom-control-description">Yes</span>
                                    </label>
                                </td>
                                <td>
                                    <b-textarea :rows="2" v-model="form.issues[index].comments" />
                                </td>
                                <td><b-btn size="sm" variant="danger" @click="removeIssue(index)"><i class="fa fa-times"></i></b-btn></td>
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
            <b-row v-if="!is_modal">
                <b-col lg="4">
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
                <b-col lg="8" class="text-right" v-if="!shift.readOnly">
                    <div v-if="!deleted">
                        <b-button variant="info" type="submit" @click="saveShift(false)">
                            <i class="fa fa-save"></i> Save <span v-if="!confirmed">Only</span><span v-else>Changes</span>
                        </b-button>
                        <b-button variant="success" type="button" @click="saveShift(true)" v-if="!confirmed">Save &amp; Confirm</b-button>
                        <b-dropdown variant="light" v-if="shift.id">
                            <template slot="button-content">
                                <i class='fa fa-list'></i> Actions
                            </template>
                            <b-dropdown-item @click="unconfirm()" v-if="confirmed"><i class="fa fa-backward"></i> Unconfirm Shift</b-dropdown-item>
                            <b-dropdown-item :href="'/business/shifts/' + shift.id + '/duplicate'"><i class="fa fa-copy"></i> Duplicate to a New Shift</b-dropdown-item>
                            <b-dropdown-divider></b-dropdown-divider>
                            <b-dropdown-item @click="deleteShift()"><i class="fa fa-times"></i> Delete Shift</b-dropdown-item>
                        </b-dropdown>
                    </div>
                </b-col>
                <b-col lg="8" class="text-right" v-else>
                    <b-button variant="light" disabled><i class="fa fa-lock"></i> This Shift is Locked For Modification</b-button>
                    <b-dropdown variant="light">
                        <template slot="button-content">
                            <i class='fa fa-list'></i> Actions
                        </template>
                        <b-dropdown-item @click="adminOverride()" v-if="admin"><i class="fa fa-save"></i> Admin Override: Save Anyways</b-dropdown-item>
                        <b-dropdown-item :href="'/business/shifts/' + shift.id + '/duplicate'"><i class="fa fa-copy"></i> Duplicate to a New Shift</b-dropdown-item>
                        <b-dropdown-divider></b-dropdown-divider>
                        <b-dropdown-item @click="deleteShift()"><i class="fa fa-times"></i> Delete Shift</b-dropdown-item>
                    </b-dropdown>
                </b-col>
            </b-row>
        </form>
    </div>
</template>

<script>
    import FormatsNumbers from '../mixins/FormatsNumbers'
    import FormatsDates from "../mixins/FormatsDates";
    import BusinessSettings from '../mixins/BusinessSettings';

    export default {
        mixins: [FormatsNumbers, FormatsDates, BusinessSettings],

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
            'admin': Number,
            'is_modal': 0,
        },
        data() {
            return {
                form: new Form(this.initForm()),
                status: (this.shift) ? this.shift.status : null,
                checked_in_time: '',
                checked_in_date: '',
                checked_out_time: '',
                checked_out_date: '',
                deleted: false,
                clients: [],
                caregivers: [],
                clientAllyPct: 0.05,
                paymentType: 'NONE',  // This is the client payment type, NOT the payment type necessarily used for this shift
                submitting: false,
            }
        },
        mounted() {
            this.loadClientCaregiverData();
            this.loadAllyPctFromClient();
            this.fixDateTimes();
        },
        computed: {
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
            allyPct() {
                return ('ally_pct' in this.shift) ? this.shift.ally_pct : this.clientAllyPct;
            },
            allyFee() {
                if (!parseFloat(this.form.caregiver_rate)) return null;
                let caregiverHourlyFloat = parseFloat(this.form.caregiver_rate);
                let providerHourlyFloat = parseFloat(this.form.provider_fee);
                let allyFee = (caregiverHourlyFloat + providerHourlyFloat) * parseFloat(this.allyPct);
                return allyFee.toFixed(2);
            },
            confirmed() {
                return this.status !== 'WAITING_FOR_CONFIRMATION' && this.status !== 'CLOCKED_IN'
            },
            rateType() {
                if (this.form.daily_rates === 0) {
                    return 'Hourly';
                }
                if (this.form.daily_rates === 1) {
                    return 'Daily';
                }
                return '';
            }
        },
        methods: {
            resetForm() {
                this.deleted = false;
                this.form = new Form(this.initForm());
                this.status = (this.shift) ? this.shift.status : null;
                this.fixDateTimes();
            },

            setDefaultDateTimes() {
                this.checked_in_date = moment().format('MM/DD/YYYY');
                this.checked_out_date = moment().format('MM/DD/YYYY');
                this.checked_in_time = '09:00';
                this.checked_out_time = '10:00';
            },

            fixDateTimes() {
                // Do not check against id below to allow for shift duplication
                if (this.shift.checked_in_time) {
                    let checkin = moment.utc(this.shift.checked_in_time).local();
                    let checkout = (this.shift.checked_out_time) ? moment.utc(this.shift.checked_out_time).local() : null;
                    this.checked_in_date = checkin.format('MM/DD/YYYY');
                    this.checked_in_time = checkin.format('HH:mm');
                    this.checked_out_date = (checkout) ? checkout.format('MM/DD/YYYY') : null;
                    this.checked_out_time = (checkout) ? checkout.format('HH:mm') : null;
                }
                else {
                    this.setDefaultDateTimes();
                }
            },

            initForm() {
                return {
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
                    daily_rates: ('daily_rates' in this.shift) ? this.shift.daily_rates : 0,
                    caregiver_rate: ('caregiver_rate' in this.shift) ? this.shift.caregiver_rate : '',
                    provider_fee: ('provider_fee' in this.shift) ? this.shift.provider_fee : '',
                    activities: this.getShiftActivityList(), //[],//('activities' in this.shift) ? this.shift.activities : [],
                    issues: ('issues' in this.shift) ? this.shift.issues : [],
                    override: false,
                    modal: this.is_modal,
                    goals: this.setupGoalsForm(),
                    questions: this.setupQuestionsForm(),
                };
            },
            createIssue() {
                this.form.issues.push({
                    caregiver_injury: 0,
                    client_injury: 0,
                    comments: '',
                    shift_id: this.shift.id,
                })
            },
            removeIssue(index) {
                this.form.issues.splice(index, 1);
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
                if (! ('activities' in this.shift)) {
                    return [];
                }

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
                        .then(response => {
                            this.deleted = true;
                            this.$emit('shift-deleted', this.shift.id);
                        });
                }
            },
            async saveShift(confirm = false) {
                this.submitting = true;
                this.form.checked_in_time = this.getClockedInMoment().format();
                this.form.checked_out_time = this.getClockedOutMoment().format();
                if (this.shift.id) {
                    try {
                        let response = await this.form.patch('/business/shifts/' + this.shift.id);
                        if (confirm) {
                            try {
                                let form = new Form();
                                let confirmResponse = await form.post('/business/shifts/' + this.shift.id + '/confirm');
                            }
                            catch (e) {
                                console.log(e);
                            }
                        }
                        this.$emit('shift-updated', this.shift.id);
                        this.submitting = false;

                    } catch (e) {
                        console.log(e);
                        this.submitting = false;
                    }
                }
                else {
                    // Create a shift (modal)
                    this.form.post('/business/shifts').then(response => {
                        this.$emit('shift-created', response.data.data.shift.id);
                        this.status = response.data.data.status;
                        this.submitting = false;
                    }).catch(error => {
                        this.submitting = false;
                    });
                }
            },
            adminOverride() {
                this.form.override = 1;
                return this.saveShift();
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
                        let newVal = field === 'checked_in_time' ?  clockin : clockout;
                        let diffFromShift = newVal.diff(moment.utc(this.shift[field]), 'minutes');
                        // debugger;
                        let diffInMinutes = clockout.diff(clockin, 'minutes');

                        this.form.clearError(field);

                        if (diffFromShift === 0) {
                            return;
                        }
                        if (diffInMinutes < 0) {
                            this.form.addError(field, 'The clocked out time cannot be less than the clocked in time.');
                        }
                        if (diffInMinutes === 0) {
                            this.form.addError(field, 'Warning: This shift is set to a duration of 0 minutes.');
                        }
                        else if (diffInMinutes > 1440) {
                            this.form.addError(field, 'Warning: This shift change exceeds a duration of 24 hours.');
                        }
                        else if (diffInMinutes > 720) {
                            this.form.addError(field, 'Warning: This shift change exceeds a duration of 12 hours.');
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
                        this.form.caregiver_rate = response.data.pivot[`caregiver_${this.rateType.toLowerCase()}_rate`];
                        this.form.provider_fee = response.data.pivot[`provider_${this.rateType.toLowerCase()}_fee`];
                    }
                });
            },
            loadAllyPctFromClient() {
                if (!this.form.client_id) return;
                axios.get('/business/clients/' + this.form.client_id + '/payment_type').then(response => {
                    this.clientAllyPct = response.data.percentage_fee;
                    this.paymentType = response.data.payment_type;
                });
            },
            changedDailyRates() {
                if (this.form.caregiver_id) {
                    this.loadCaregiverRates();
                    alert('You have just changed the shift type.  Please verify the caregiver and provider rates match what is expected for billing purposes.');
                }
            },
            /**
             * Initialize goals object/array form values with the actual ones
             * attached to the shift (if any).
             */
            setupGoalsForm() {
                let goals = {};
                if (this.shift.client) {
                    this.shift.client.goals.forEach(item => {
                        let val = '';
                        let index = this.shift.goals.findIndex(obj => obj.pivot.client_goal_id == item.id);
                        if (index >= 0) {
                            val = this.shift.goals[index].pivot.comments;
                        }
                        goals[item.id] = val;
                    });
                }
                return goals;
            },
            /**
             * Initialize questions object/array form values with the actual ones
             * attached to the shift (if any).
             */
            setupQuestionsForm() {
                let questions = {};
                if (this.shift.questions) {
                    this.shift.questions.forEach(item => {
                        questions[item.id] = item.pivot.answer;
                    });
                }
                return questions;
            },
        },
        watch: {
            shift(newVal, oldVal) {
                this.resetForm();
            },
            checked_in_date(val, old) {
                if (old) {
                    this.validateTimeDifference('checked_in_time');
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
            },
        },
    }
</script>

<style>
    .deletedForm {
        opacity: 0.3;
    }
</style>

