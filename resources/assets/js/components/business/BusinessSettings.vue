<template>
    <b-card no-body>
        <b-tabs pills card>
            <b-tab title="System" active>
                <b-row>
                    <b-col lg="6">
                        <!--<div v-if="business.logo" class="mb-3">-->
                        <!--{{ business.logo }}-->
                        <!--</div>-->
                        <!--<b-alert v-else show variant="info">Logo not set.</b-alert>-->
                        <!--<b-form-group label="Logo">-->
                        <!--<b-form-file id="logo" v-model="businessSettings.logo" tabindex="0"></b-form-file>-->
                        <!--</b-form-group>-->
                        <b-form-group label="Mileage Rate" label-for="mileageRate">
                            <b-form-input type="number"
                                          step="any"
                                          id="mileageRate"
                                          tabindex="1"
                                          v-model="businessSettings.mileage_rate">
                            </b-form-input>
                            <input-help :form="businessSettings" field="mileageRate" text="Enter the amount reimbursed for each mile, 0 will disable mileage reimbursements"></input-help>
                        </b-form-group>
                        <b-form-group label="Auto-Confirm Shifts" label-for="auto_confirm">
                            <b-form-select id="auto_confirm"
                                           v-model="businessSettings.auto_confirm"
                            >
                                <option value="0">No</option>
                                <option value="1">Yes</option>
                            </b-form-select>
                            <input-help :form="businessSettings" field="auto_confirm" text="Automatically confirm shifts that are clocked in on the app or telephony."></input-help>
                        </b-form-group>
                        <b-form-group label="Ask on Confirmation" label-for="ask_on_confirm">
                            <b-form-select id="ask_on_confirm"
                                           v-model="businessSettings.ask_on_confirm"
                            >
                                <option value="0">No</option>
                                <option value="1">Yes</option>
                            </b-form-select>
                            <input-help :form="businessSettings" field="ask_on_confirm" text="Display a confirmation box before confirming or unconfirming a shift."></input-help>
                        </b-form-group>

                        <b-form-group label="Allow Manual Timesheets" label-for="allows_manual_shifts">
                            <b-form-select id="allows_manual_shifts"
                                           v-model="businessSettings.allows_manual_shifts"
                            >
                                <option value="0">No</option>
                                <option value="1">Yes</option>
                            </b-form-select>
                            <input-help :form="businessSettings" field="allows_manual_shifts" text="Allow Caregivers to submit shift information manually."></input-help>
                        </b-form-group>

                        <b-form-group label="Manual Timesheet Exceptions" label-for="timesheet_exceptions">
                            <b-form-select id="timesheet_exceptions"
                                           v-model="businessSettings.timesheet_exceptions"
                            >
                                <option value="0">No</option>
                                <option value="1">Yes</option>
                            </b-form-select>
                            <input-help :form="businessSettings" field="timesheet_exceptions" text="Generate an exception when a manual timesheet is entered by a caregiver."></input-help>
                        </b-form-group>

                        <b-form-group label="Unverified Location Exceptions" label-for="location_exceptions">
                            <b-form-select id="location_exceptions"
                                           v-model="businessSettings.location_exceptions"
                            >
                                <option value="0">No</option>
                                <option value="1">Yes</option>
                            </b-form-select>
                            <input-help :form="businessSettings" field="location_exceptions" text="Generate an exception when a mobile app shift is not verified through geolocation."></input-help>
                        </b-form-group>

                    </b-col>
                    <b-col lg="6">
                        <b-form-group label="Scheduling" label-for="scheduling">
                            <b-form-select id="scheduling"
                                           v-model="businessSettings.scheduling"
                                           disabled>
                                <option value="1">Enabled</option>
                                <option value="0">Disabled</option>
                            </b-form-select>
                            <input-help :form="businessSettings" field="scheduling" text="Enable or disable shift scheduling functionality"></input-help>
                        </b-form-group>
                        <b-form-group label="Calendar Default View" label-for="calendar_default_view">
                            <b-form-select id="calendar_default_view"
                                           v-model="businessSettings.calendar_default_view">
                                <option value="month">Month</option>
                                <option value="agendaWeek">Week</option>
                            </b-form-select>
                            <input-help :form="businessSettings" field="calendar_default_view" text="Choose the default view for the schedule"></input-help>
                        </b-form-group>
                        <b-form-group label="Default Schedule Caregiver Filter" label-for="calendar_caregiver_filter">
                            <b-form-select id="calendar_caregiver_filter"
                                           v-model="businessSettings.calendar_caregiver_filter">
                                <option value="all">All Caregivers</option>
                                <option value="unassigned">Open Shifts</option>
                            </b-form-select>
                            <input-help :form="businessSettings" field="calendar_caregiver_filter" text="Choose the default caregiver filter for the schedule"></input-help>
                            <small class="text-warning" v-if="businessSettings.calendar_caregiver_filter === 'all'">Warning: We do not recommend using 'All Caregivers' for larger registries.</small>
                        </b-form-group>
                        <b-form-group label="Remember Schedule Filters" label-for="calendar_remember_filters">
                            <b-form-select id="calendar_remember_filters"
                                           v-model="businessSettings.calendar_remember_filters">
                                <option value="1">Yes</option>
                                <option value="0">No</option>
                            </b-form-select>
                            <input-help :form="businessSettings" field="calendar_remember_filters" text="Remember the last filters used when loading the schedule."></input-help>
                        </b-form-group>
                        <b-form-group label="Calendar Next Day Threshold" label-for="calendar_next_day_threshold">
                            <b-form-input id="calendar_next_day_threshold"
                                           v-model="businessSettings.calendar_next_day_threshold"
                                           />
                            <input-help :form="businessSettings" field="calendar_next_day_threshold" text="When an shifts’s end time crosses into another day, the minimum time it must be in order for it to show on that day."></input-help>
                        </b-form-group>
                    </b-col>
                </b-row>
                <b-row>
                    <b-col lg="12">
                        <b-form-group>
                            <b-button @click="update" variant="info">Save</b-button>
                        </b-form-group>
                    </b-col>
                </b-row>
            </b-tab>
            <b-tab title="Phone &amp; Address">
                <b-row>
                    <b-col lg="6">
                        <b-form-group label="Phone 1">
                            <b-form-input v-model="businessSettings.phone1"
                                          tabindex="5"
                                          id="phone1">
                            </b-form-input>
                        </b-form-group>
                        <b-form-group label="Address 1">
                            <b-form-input v-model="businessSettings.address1"
                                          tabindex="7"
                                          id="phone2">
                            </b-form-input>
                        </b-form-group>
                        <b-form-group label="City">
                            <b-form-input v-model="businessSettings.city"
                                          tabindex="9"
                                          id="city">
                            </b-form-input>
                        </b-form-group>
                        <b-form-group label="Zip">
                            <b-form-input v-model="businessSettings.zip"
                                          tabindex="11"
                                          id="zip">
                            </b-form-input>
                        </b-form-group>
                        <b-form-group label="Timezone">
                            <b-form-select id="timezone" v-model="businessSettings.timezone" tabindex="13">
                                <option v-for="timezone in timezones" :value="timezone" :key="timezone">{{ timezone }}</option>
                            </b-form-select>
                        </b-form-group>
                        <b-btn @click="update" variant="info">
                            Save
                        </b-btn>
                    </b-col>
                    <b-col lg="6">
                        <b-form-group label="Phone 2">
                            <b-form-input v-model="businessSettings.phone2"
                                          tabindex="6"
                                          id="phone2">
                            </b-form-input>
                        </b-form-group>
                        <b-form-group label="Address 2">
                            <b-form-input v-model="businessSettings.address2"
                                          tabindex="8"
                                          id="address2">
                            </b-form-input>
                        </b-form-group>
                        <b-form-group label="State">
                            <b-form-input v-model="businessSettings.state"
                                          tabindex="10"
                                          id="state">
                            </b-form-input>
                        </b-form-group>
                        <b-form-group label="Country">
                            <b-form-input v-model="businessSettings.country"
                                          tabindex="12"
                                          id="country">
                            </b-form-input>
                        </b-form-group>
                    </b-col>
                </b-row>
            </b-tab>
            <b-tab title="Clock Out Questions">
                <b-row>
                    <b-col lg="12">
                        <h4>Options</h4>
                        <hr />
                    </b-col>
                    <b-col lg="6">
                        <b-form-group label="Allow Recording of Mileage?" label-for="co_mileage">
                            <b-form-select id="co_mileage"
                                           v-model="businessSettings.co_mileage">
                                <option value="1">Yes</option>
                                <option value="0">No</option>
                            </b-form-select>
                            <input-help :form="businessSettings" field="co_mileage" text=""></input-help>
                        </b-form-group>
                        <b-form-group label="Allow Recording of Other Expenses?" label-for="co_expenses">
                            <b-form-select id="co_expenses"
                                           v-model="businessSettings.co_expenses">
                                <option value="1">Yes</option>
                                <option value="0">No</option>
                            </b-form-select>
                            <input-help :form="businessSettings" field="co_expenses" text=""></input-help>
                        </b-form-group>

                    </b-col>
                    <b-col lg="6">
                        <b-form-group label="Allow General Comments / Notes?" label-for="co_comments">
                            <b-form-select id="co_comments"
                                           v-model="businessSettings.co_comments">
                                <option value="1">Yes</option>
                                <option value="0">No</option>
                            </b-form-select>
                            <input-help :form="businessSettings" field="co_comments" text=""></input-help>
                        </b-form-group>
                        <b-form-group label="Client Signature" label-for="signatureOption">
                            <b-form-select id="signatureOption"
                                           v-model="signatureOption">
                                <option value="do_not_show">Do Not Show</option>
                                <option value="show">Show, Do Not Require</option>
                                <option value="required">Show &amp; Require</option>
                            </b-form-select>
                            <input-help :form="businessSettings" field="co_signature" text=""></input-help>
                        </b-form-group>
                    </b-col>
                </b-row>
                <b-row>
                    <b-col lg="12">
                        <h4>System Questions</h4>
                        <hr />
                    </b-col>
                    <b-col lg="6">
                        <b-form-group label="Were you injured on your shift?" label-for="co_injuries">
                            <b-form-select id="co_injuries"
                                           v-model="businessSettings.co_injuries">
                                <option value="1">Show</option>
                                <option value="0">Do Not Show</option>
                            </b-form-select>
                            <input-help :form="businessSettings" field="co_injuries" text=""></input-help>
                        </b-form-group>
                    </b-col>
                    <b-col lg="6">
                        <b-form-group label="Were there any other issues on your shift?" label-for="co_issues">
                            <b-form-select id="co_issues"
                                           v-model="businessSettings.co_issues">
                                <option value="1">Show</option>
                                <option value="0">Do Not Show</option>
                            </b-form-select>
                            <input-help :form="businessSettings" field="co_issues" text=""></input-help>
                        </b-form-group>
                    </b-col>
                </b-row>
                <b-row>
                    <b-col lg="12">
                        <h4>Custom Questions</h4>
                        <hr />
                    </b-col>
                    <b-col lg="12" class="with-padding-bottom">
                        <center>Coming soon</center>
                    </b-col>
                </b-row>
                <b-row>
                    <b-col lg="12">
                        <b-btn @click="update" variant="info">
                            Save
                        </b-btn>
                    </b-col>
                </b-row>
            </b-tab>
        </b-tabs>
    </b-card>
</template>

<script>
    export default {
        props: {
            'business': {
                type: Object,
                default() {
                    return {};
                }
            },
        },

        data() {
            return {
                businessSettings: new Form({
                    //logo: this.business.logo,
                    scheduling: this.business.scheduling,
                    mileage_rate: this.business.mileage_rate,
                    calendar_default_view: this.business.calendar_default_view,
                    calendar_caregiver_filter: this.business.calendar_caregiver_filter,
                    calendar_remember_filters: this.business.calendar_remember_filters,
                    calendar_next_day_threshold: this.business.calendar_next_day_threshold,
                    phone1: this.business.phone1,
                    phone2: this.business.phone2,
                    address1: this.business.address1,
                    address2: this.business.address2,
                    city: this.business.city,
                    state: this.business.state,
                    zip: this.business.zip,
                    country: this.business.country,
                    timezone: this.business.timezone,
                    auto_confirm: this.business.auto_confirm,
                    ask_on_confirm: this.business.ask_on_confirm,
                    allows_manual_shifts: this.business.allows_manual_shifts,
                    location_exceptions: this.business.location_exceptions,
                    timesheet_exceptions: this.business.timesheet_exceptions,
                    require_signatures: this.business.require_signatures,
                    co_mileage: this.business.co_mileage,
                    co_injuries: this.business.co_injuries,
                    co_comments: this.business.co_comments,
                    co_expenses: this.business.co_expenses,
                    co_issues: this.business.co_issues,
                    co_signature: this.business.co_signature,
                }),
                timezones: [
                    "America/New_York",
                    "America/Chicago",
                    "America/Denver",
                    "America/Phoenix",
                    "America/Los_Angeles"
                ],
                signatureMapping: {
                    required: {
                        co_signature: 1,
                        require_signatures: 1,
                    },
                    show: {
                        co_signature: 1,
                        require_signatures: 0,
                    },
                    do_not_show: {
                        co_signature: 0,
                        require_signatures: 0,
                    }
                },
                signatureOption: null,
            }
        },

        mounted() {
            this.signatureOption = this.getSignatureOption(this.business);
        },

        methods: {
            update() {
                this.businessSettings.put('/business/settings/' + this.business.id);
            },
            getSignatureOption(business) {
                for (var option of Object.keys(this.signatureMapping)) {
                    let obj = this.signatureMapping[option];
                    console.log(option, obj);
                    if (business.require_signatures === obj.require_signatures && business.co_signature === obj.co_signature) {
                        return option;
                    }
                }
            },
            updateSignatureValues() {
                if (!this.signatureOption) return;
                Object.assign(this.businessSettings, this.signatureMapping[this.signatureOption]);
            }
        },

        watch: {
            signatureOption() { this.updateSignatureValues() },
        }
    }
</script>