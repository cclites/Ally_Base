<template>
    <div>
        <b-card>
            <b-row>
                <b-col md="12">
                    <b-form-group label="Pay Cycle" label-for="pay_cycle">
                        <b-form-radio-group id="pay_cycle" name="pay_cycle" v-model="form.pay_cycle">
                            <b-form-radio value="weekly">Weekly</b-form-radio>
                            <b-form-radio value="bi-weekly">Bi-Weekly</b-form-radio>
                            <b-form-radio value="monthly">Monthly</b-form-radio>
                        </b-form-radio-group>
                        <input-help :form="form" text="How often do you pay your caregivers?"></input-help>
                    </b-form-group>

                    <b-form-group label="Last Day of Cycle" label-for="last_day_of_cycle">
                        <b-form-radio-group id="last_day_of_cycle" name="last_day_of_cycle" v-model="form.last_day_of_cycle">
                            <b-form-radio value="last day of every month">On the midpoint and last day of every month</b-form-radio>
                            <b-form-radio value="monday">Monday</b-form-radio>
                            <b-form-radio value="tuesday">Tuesday</b-form-radio>
                            <b-form-radio value="wednesday">Wednesday</b-form-radio>
                            <b-form-radio value="thursday">Thursday</b-form-radio>
                            <b-form-radio value="friday">Friday</b-form-radio>
                            <b-form-radio value="saturday">Saturday</b-form-radio>
                            <b-form-radio value="sunday">Sunday</b-form-radio>
                        </b-form-radio-group>
                        <input-help :form="form" text="What is the last day of the pay cycle?"></input-help>
                    </b-form-group>

                    <b-form-group label="Last Day of First Period" label-for="last_day_of_first_period">
                        <date-picker class="w-200" v-model="last_day_of_first_period"></date-picker>
                        <input-help :form="form" text="Please select the last day of the first period you want to track?"></input-help>
                    </b-form-group>
                </b-col>
            </b-row>
        </b-card>
        <b-card>
            <b-row>
                <b-col md="12">
                    <b-form-group label="Mileage Reimbursement Rate" label-for="mileage_reimbursement_rate">
                        $ <b-form-input type="number" min="0" class="w-200 d-inline-block" id="mileage_reimbursement_rate" v-model="form.mileage_reimbursement_rate"></b-form-input> / mile
                        <input-help :form="form" text="What is your company's mileage reimbursement rate?"></input-help>
                    </b-form-group>
                </b-col>
            </b-row>
        </b-card>
        <b-card>
            <b-row>
                <b-col md="12">
                    <b-form-group label="Unpaired Pay Rates" label-for="unpaired_pay_rates">
                        <input-help :form="form" text="These are pay rates that do not have a corresponding bill rate. Most agencies primarily use the Rates Schedule to specify billing and payroll rates."></input-help>
                        <b-form-checkbox class="d-block" id="unpaired_pay_rates_hourly" v-model="form.unpaired_pay_rates" value="Hourly">Hourly</b-form-checkbox>
                        <b-form-checkbox class="d-block" id="unpaired_pay_rates_per_visit" v-model="form.unpaired_pay_rates" value="Per Visit">Per Visit</b-form-checkbox>
                        <b-form-checkbox class="d-block" :disabled="true" id="unpaired_pay_rates_live_in" v-model="form.unpaired_pay_rates" value="Live In">Live In</b-form-checkbox>

                        <div v-if="form.unpaired_pay_rates.indexOf('Live In') != -1">
                            <div v-for="(item, index) in form.liveInRates" class="ml-3">
                                <span class="mdi mdi-minus remove" @click="removeLiveInRates(index)"></span>
                                $ <b-form-input type="number" min="0" class="w-100 d-inline-block" v-model="item.rate"></b-form-input> /day
                                <b-form-input type="text" class="w-200 d-inline-block" v-model="item.name"></b-form-input>
                                <b-form-checkbox class="d-inline-block" v-model="item.prorate" value="Hourly">Prorate live in rate</b-form-checkbox>
                            </div>
                            <a href="javascript:;" class="ml-3 mt-1 d-block" @click="addLiveInRate"><span class="mdi mdi-plus"></span> Add Live In Rate</a>
                        </div>
                    </b-form-group>
                </b-col>
            </b-row>
        </b-card>
        <b-card>
            <div>Overtime Settings</div>
            <p class="overtime-text">
                Different wage and hour laws apply in different states and can vary be city abd county within a state,
                and also vary depending upon a caregiver's role(for example, whether or not the employee is a "personal attendant" or a live-in caregiver).
                ClearCare's tools provide data that can help you and your payroll provider calculate overtime, but they are not a substitute for legal
                counsel or an experienced payroll provider.ClearCare is not responsible  for ensuring that overtime payments are
                calculated in compliance with state and federal labor laws.
                <br><br>
                Please select the default overtime calculation rules for overtime for your caregivers, or leave the section below blank if you will manually calculate
                overtime outside of the  ClearCare system.<br>Please consult an appropriate professional advisor to ensure you are in compliance with all applicable labor laws.
            </p>

            <b-form-group label="Overtime Will Be Automatically Calculated For:" label-for="overtime_automatically_calc" class="mt-4">
                <div>
                    <b-form-checkbox class="d-inline-block" v-model="form.overtime" value="overtime_hours_day">An work above</b-form-checkbox>
                    <b-form-input type="number" min="0" :disabled="form.overtime.indexOf('overtime_hours_day') == -1" class="w-60 d-inline-block mr-1" v-model="form.overtime_hours_day"></b-form-input>
                    hours in a given day
                </div>

                <div class="mt-2">
                    <b-form-checkbox class="d-inline-block" v-model="form.overtime" value="overtime_hours_week">An work above</b-form-checkbox>
                    <b-form-input type="number" min="0" :disabled="form.overtime.indexOf('overtime_hours_week') == -1" class="w-60 d-inline-block mr-1" v-model="form.overtime_hours_week"></b-form-input>
                    hours in a given week
                </div>

                <div class="mt-2">
                    <b-form-checkbox class="d-inline-block" v-model="form.overtime" value="overtime_consecutive_days">An work done for <u>more  than</u></b-form-checkbox>
                    <b-form-select :disabled="form.overtime.indexOf('overtime_consecutive_days') == -1" class="w-60 d-inline-block mr-1" v-model="form.overtime_consecutive_days">
                        <option value="5">5</option>
                    </b-form-select>
                    consecutive days in any given week
                </div>
            </b-form-group>

            <b-form-group label="Double Overtime Will Be Automatically Calculated For:" label-for="dbl_overtime_automatically_calc">
                <div>
                    <b-form-checkbox class="d-inline-block" v-model="form.dbl_overtime" value="dbl_overtime_hours_day">An work above</b-form-checkbox>
                    <b-form-input type="number" min="0" :disabled="form.dbl_overtime.indexOf('dbl_overtime_hours_day') == -1" class="w-60 d-inline-block mr-1" v-model="form.dbl_overtime_hours_day"></b-form-input>
                    hours in a given day
                </div>

                <div class="mt-2">
                    <b-form-checkbox class="d-inline-block" v-model="form.dbl_overtime" value="dbl_overtime_consecutive_days">An work done over</b-form-checkbox>
                    <b-form-input type="number" min="0" :disabled="form.dbl_overtime.indexOf('dbl_overtime_consecutive_days') == -1" class="w-60 d-inline-block mr-1" v-model="form.dbl_overtime_consecutive_days"></b-form-input>
                    hours in a day on the 7th consecutive day  worked
                </div>
            </b-form-group>

            <b-form-group label="Overtime Method" label-for="overtime_method">
                <b-form-select class="w-200" v-model="form.overtime_method">
                    <option value="straight_time">Straight Time</option>
                </b-form-select>
                <input-help :form="form" text="Which method do you use to pay your Caregivers for overtime?"></input-help>
            </b-form-group>
        </b-card>
        <b-card>
            <div>In-Facility Overtime Rules</div>
            <p class="overtime-text">Some states require agencies to adhere to separate overtime rules for at-home and in-facility care. Click here to set up separate in-facility overtime rules.</p>
            <b-button :variant="'success'">Set Up In-Facility Overtime Rules...</b-button>
        </b-card>
        <b-button :variant="'info'" @click="save()" v-if="!loading">Save Changes</b-button>
        <div class="c-loader" v-if="loading"></div>
    </div>


</template>

<script>
    export default {

        props: {
            business: Object
        },

        data() {
            return {
                form: new Form({
                    pay_cycle: this.business.pay_cycle ? this.business.pay_cycle :'weekly',
                    last_day_of_cycle: this.business.last_day_of_cycle ? this.business.last_day_of_cycle : 'last day of every month',
                    last_day_of_first_period: this.business.last_day_of_first_period ? this.business.last_day_of_first_period : '',
                    mileage_reimbursement_rate: this.business.mileage_reimbursement_rate ? this.business.mileage_reimbursement_rate : '',
                    unpaired_pay_rates: this.business.unpaired_pay_rates ? JSON.parse(this.business.unpaired_pay_rates) : [],
                    overtime: [],
                    overtime_hours_day: '',
                    overtime_hours_week: '',
                    overtime_consecutive_days: '5',
                    dbl_overtime: [],
                    dbl_overtime_hours_day: '',
                    dbl_overtime_consecutive_days: '',
                    overtime_method: this.business.overtime_method ? this.business.overtime_method : 'straight_time',
                    liveInRates: []
                }),
                last_day_of_first_period: moment().format('MM/DD/YYYY'),
                loading: false,
            }
        },

        mounted() {
            this.fetchOvertimeData('overtime', 'hours_day');
            this.fetchOvertimeData('overtime', 'hours_week');
            this.fetchOvertimeData('overtime', 'consecutive_days');
            this.fetchOvertimeData('dbl_overtime', 'hours_day');
            this.fetchOvertimeData('dbl_overtime', 'consecutive_days');
        },

        methods: {
            save() {
                this.loading = true;
                if(this.last_day_of_first_period != '') {
                    this.form.last_day_of_first_period = moment(this.last_day_of_first_period).format('YYYY-MM-DD');
                }

                this.form.put('update-payroll-policy/' + this.business.id).then(data => {
                    this.loading = false;
                }).catch(err => {
                    this.loading = false;
                });
            },

            addLiveInRate() {
                this.form.liveInRates.push({
                    rate: '',
                    name: '',
                    prorate: false
                })
            },

            fetchOvertimeData(type, hoursType) {
                if(this.business[type + '_' +  hoursType]) {
                    this.form[type].push(type + '_' +  hoursType);
                    this.form[type + '_' +  hoursType] = this.business[type + '_' +  hoursType];
                }
            },

            removeLiveInRates(index) {
                if(this.liveInRates[index]) {
                    this.liveInRates.splice(index, 1);
                }
            }
        },
    }
</script>

<style lang="scss">
    .w-60 {
        width: 60px !important;
    }

    .w-100 {
        width: 100px !important;
    }

    .w-200 {
        width: 200px !important;
    }

    .remove {
        font-size: 30px;
        border-radius: 50%;
        height: 45px;
        width: 45px;
        line-height: 45px;
        display: inline-block;
        text-align: center;
        cursor: pointer;
        transition: background-color .4s;

        &:hover {
            background-color: #ffb8b8;
        }
    }

    .overtime-text {
        font-size: 12px;
        font-style: italic;
        margin-top: 5px;
        margin-bottom: 10px;
    }

    .fs-i {
        font-style: italic;
    }
</style>