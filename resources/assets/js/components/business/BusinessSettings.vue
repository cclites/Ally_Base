<template>
    <div>
        <b-card header="Select an Office Location" header-variant="info">
            <b-row>
                <b-col lg="4" md="6">
                    <business-location-select v-model="businessId"></business-location-select>
                </b-col>
            </b-row>
        </b-card>
        <b-card no-body v-if="business.id">
            <b-tabs pills card v-model="tabIndex">
                <b-tab title="System" href="#system">
                    <b-row>
                        <b-col lg="6">
                            <!--<div v-if="business.logo" class="mb-3">-->
                            <!--{{ business.logo }}-->
                            <!--</div>-->
                            <!--<b-alert v-else show variant="info">Logo not set.</b-alert>-->
                            <!--<b-form-group label="Logo">-->
                            <!--<b-form-file id="logo" v-model="businessSettings.logo" tabindex="0"></b-form-file>-->
                            <!--</b-form-group>-->
                            <b-form-group label="Mileage Rate" label-for="mileageRate" label-class="required">
                                <b-form-input type="number"
                                              step="any"
                                              id="mileageRate"
                                              tabindex="1"
                                              v-model="businessSettings.mileage_rate">
                                </b-form-input>
                                <input-help :form="businessSettings" field="mileageRate"
                                            text="Enter the amount reimbursed for each mile, 0 will disable mileage reimbursements"></input-help>
                            </b-form-group>
                            <b-form-group label="Allow Manual Timesheets" label-for="allows_manual_shifts">
                                <b-form-select id="allows_manual_shifts"
                                               v-model="businessSettings.allows_manual_shifts"
                                >
                                    <option :value="0">No</option>
                                    <option :value="1">Yes</option>
                                </b-form-select>
                                <input-help :form="businessSettings" field="allows_manual_shifts"
                                            text="Allow Caregivers to submit shift information manually."></input-help>
                            </b-form-group>

                            <b-form-group label="Manual Timesheet Exceptions" label-for="timesheet_exceptions" label-class="required">
                                <b-form-select id="timesheet_exceptions"
                                               v-model="businessSettings.timesheet_exceptions"
                                >
                                    <option :value="0">No</option>
                                    <option :value="1">Yes</option>
                                </b-form-select>
                                <input-help :form="businessSettings" field="timesheet_exceptions"
                                            text="Generate an exception when a manual timesheet is entered by a caregiver."></input-help>
                            </b-form-group>

                            <b-form-group label="Unverified Location Exceptions" label-for="location_exceptions"  label-class="required">
                                <b-form-select id="location_exceptions"
                                               v-model="businessSettings.location_exceptions"
                                >
                                    <option :value="0">No</option>
                                    <option :value="1">Yes</option>
                                </b-form-select>
                                <input-help :form="businessSettings" field="location_exceptions"
                                            text="Generate an exception when a mobile app shift is not verified through geolocation."></input-help>
                            </b-form-group>

                            <b-form-group label="Shift Rounding Method" label-for="shift_rounding_method" label-class="required">
                                <b-form-select id="shift_rounding_method"
                                               v-model="businessSettings.shift_rounding_method"
                                >
                                    <option value="none">No Rounding</option>
                                    <option value="shift">Entire Shift</option>
                                    <option value="individual">Individual Clock In/Out</option>
                                </b-form-select>
                                <input-help :form="businessSettings" field="shift_rounding_method"
                                            text="Select the methodology used to round the number of hours worked on each shift."></input-help>
                            </b-form-group>

                            <b-form-group label="Enable Client Onboarding" label-for="enable_client_onboarding">
                                <b-form-select id="enable_client_onboarding"
                                               v-model="businessSettings.enable_client_onboarding"
                                >
                                    <option :value="0">No</option>
                                    <option :value="1">Yes</option>
                                </b-form-select>
                                <input-help :form="businessSettings" field="enable_client_onboarding"
                                            text="Enable the client onboarding button on the client details page."></input-help>
                            </b-form-group>
                        </b-col>
                        <b-col lg="6">
                            <b-form-group label="Scheduling" label-for="scheduling" label-class="required">
                                <b-form-select id="scheduling"
                                               v-model="businessSettings.scheduling"
                                               disabled>
                                    <option :value="1">Enabled</option>
                                    <option :value="0">Disabled</option>
                                </b-form-select>
                                <input-help :form="businessSettings" field="scheduling"
                                            text="Enable or disable shift scheduling functionality"></input-help>
                            </b-form-group>
                            <b-form-group label="Calendar Default View" label-for="calendar_default_view" label-class="required">
                                <b-form-select id="calendar_default_view"
                                               v-model="businessSettings.calendar_default_view">
                                    <option value="month">Month</option>
                                    <option value="timelineWeek">Week</option>
                                    <option value="timelineDay">Day</option>
                                </b-form-select>
                                <input-help :form="businessSettings" field="calendar_default_view"
                                            text="Choose the default view for the schedule"></input-help>
                            </b-form-group>
                            <b-form-group label="Default Schedule Caregiver Filter" label-for="calendar_caregiver_filter" label-class="required">
                                <b-form-select id="calendar_caregiver_filter"
                                               v-model="businessSettings.calendar_caregiver_filter">
                                    <option value="all">All Caregivers</option>
                                    <option value="unassigned">Open Shifts</option>
                                </b-form-select>
                                <input-help :form="businessSettings" field="calendar_caregiver_filter"
                                            text="Choose the default caregiver filter for the schedule"></input-help>
                                <small class="text-warning" v-if="businessSettings.calendar_caregiver_filter === 'all'">
                                    Warning: We do not recommend using 'All Caregivers' for monthly views.
                                </small>
                            </b-form-group>
                            <b-form-group label="Remember Schedule Filters" label-for="calendar_remember_filters" label-class="required">
                                <b-form-select id="calendar_remember_filters"
                                               v-model="businessSettings.calendar_remember_filters">
                                    <option :value="1">Yes</option>
                                    <option :value="0">No</option>
                                </b-form-select>
                                <input-help :form="businessSettings" field="calendar_remember_filters"
                                            text="Remember the last filters used when loading the schedule."></input-help>
                            </b-form-group>
                            <b-form-group label="Calendar Span Multiple Days" label-for="calendar_next_day_threshold" label-class="required">
                                <b-form-select id="calendar_next_day_threshold"
                                               v-model="businessSettings.calendar_next_day_threshold">
                                    <option value="00:15:00">Yes</option>
                                    <option value="23:59:00">No</option>
                                </b-form-select>
                                <input-help :form="businessSettings" field="calendar_next_day_threshold"
                                            text="When an shift’s end time crosses midnight, show the shift across both days in the calendar."></input-help>
                            </b-form-group>
                        </b-col>
                    </b-row>
                    <b-row>
                        <b-col>
                            <hr />
                            <b-btn @click="update" variant="info" size="lg">
                                Save Settings
                            </b-btn>
                        </b-col>
                    </b-row>
                </b-tab>
                <b-tab title="Phone &amp; Address" href="#phone">
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
                            <b-form-group label="Timezone" label-class="required">
                                <b-form-select id="timezone" v-model="businessSettings.timezone" tabindex="13">
                                    <option v-for="timezone in timezones" :value="timezone" :key="timezone">{{ timezone
                                        }}
                                    </option>
                                </b-form-select>
                            </b-form-group>
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
                    <b-row>
                        <b-col>
                            <hr />
                            <b-btn @click="update" variant="info" size="lg">
                                Save Settings
                            </b-btn>
                        </b-col>
                    </b-row>
                </b-tab>
                <b-tab title="Medicaid" href="#medicaid">
                    <b-row>
                        <b-col lg="6">
                            <b-form-group label="EIN">
                                <b-form-input v-model="businessSettings.ein">
                                </b-form-input>
                            </b-form-group>
                            <b-form-group label="Medicaid ID">
                                <b-form-input v-model="businessSettings.medicaid_id">
                                </b-form-input>
                            </b-form-group>
                        </b-col>
                        <b-col lg="6">
                            <b-form-group label="NPI Number">
                                <b-form-input v-model="businessSettings.medicaid_npi_number">
                                </b-form-input>
                            </b-form-group>
                            <b-form-group label="NPM Taxonomy">
                                <b-form-input v-model="businessSettings.medicaid_npi_taxonomy">
                                </b-form-input>
                            </b-form-group>
                        </b-col>
                    </b-row>
                    <b-row>
                        <b-col>
                            <hr />
                            <b-btn @click="update" variant="info" size="lg">
                                Save Settings
                            </b-btn>
                        </b-col>
                    </b-row>
                </b-tab>
                <b-tab title="Clock Out Questions" href="#questions">
                    <b-row>
                        <b-col md="12">
                            <b-alert show variant="info">
                                Note: These options only affect our mobile app.  Any changes here will not be reflected on your telephony system.
                            </b-alert>
                        </b-col>
                        <b-col lg="12">
                            <h4>Options</h4>
                            <hr/>
                        </b-col>
                        <b-col lg="6">
                            <b-form-group label="Allow Recording of Mileage?" label-for="co_mileage" label-class="required">
                                <b-form-select id="co_mileage"
                                               v-model="businessSettings.co_mileage">
                                    <option :value="1">Yes</option>
                                    <option :value="0">No</option>
                                </b-form-select>
                                <input-help :form="businessSettings" field="co_mileage" text=""></input-help>
                            </b-form-group>
                            <b-form-group label="Allow Recording of Other Expenses?" label-for="co_expenses" label-class="required">
                                <b-form-select id="co_expenses"
                                               v-model="businessSettings.co_expenses">
                                    <option :value="1">Yes</option>
                                    <option :value="0">No</option>
                                </b-form-select>
                                <input-help :form="businessSettings" field="co_expenses" text=""></input-help>
                            </b-form-group>

                        </b-col>
                        <b-col lg="6">
                            <b-form-group label="Allow General Comments / Notes?" label-for="co_comments" label-class="required">
                                <b-form-select id="co_comments"
                                               v-model="businessSettings.co_comments">
                                    <option :value="1">Yes</option>
                                    <option :value="0">No</option>
                                </b-form-select>
                                <input-help :form="businessSettings" field="co_comments" text=""></input-help>
                            </b-form-group>
                            <b-form-group label="Client Signature" label-for="signatureOption" label-class="required">
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
                            <hr/>
                        </b-col>
                        <b-col lg="6">
                            <b-form-group label="Were you injured on your shift?" label-for="co_injuries" label-class="required">
                                <b-form-select id="co_injuries"
                                               v-model="businessSettings.co_injuries">
                                    <option :value="1">Show</option>
                                    <option :value="0">Do Not Show</option>
                                </b-form-select>
                                <input-help :form="businessSettings" field="co_injuries" text=""></input-help>
                            </b-form-group>
                        </b-col>
                        <b-col lg="6">
                            <b-form-group label="Were there any other issues on your shift?" label-for="co_issues" label-class="required">
                                <b-form-select id="co_issues"
                                               v-model="businessSettings.co_issues">
                                    <option :value="1">Show</option>
                                    <option :value="0">Do Not Show</option>
                                </b-form-select>
                                <input-help :form="businessSettings" field="co_issues" text=""></input-help>
                            </b-form-group>
                        </b-col>
                    </b-row>
                    <b-row>
                        <b-col>
                            <hr />
                            <b-btn @click="update" variant="info" size="lg">
                                Save Settings
                            </b-btn>
                        </b-col>
                    </b-row>
                    <b-row>
                        <b-col lg="12" class="mt-4">
                            <h4>Custom Questions</h4>
                        </b-col>
                        <b-col lg="12" class="with-padding-bottom">
                            <question-list :business="business"/>
                        </b-col>
                    </b-row>
                </b-tab>
                <b-tab title="Payroll Policy" href="#payroll" v-if="business.type == 'Agency'">
                    <payroll-policy :business="business"></payroll-policy>
                </b-tab>
                <b-tab title="Shift Confirmations" href="#shift-confirmations">
                    <b-row>
                        <b-col lg="6">
                            <b-form-group label="Allow clients to confirm and modify visits" label-for="allow_client_confirmations" label-class="required">
                                 <b-form-select id="allow_client_confirmations"
                                               :disabled="businessSettings.auto_confirm == 1"
                                               v-model="businessSettings.allow_client_confirmations"
                                >
                                    <option :value="0">No</option>
                                    <option :value="1">Yes</option>
                                </b-form-select>
                            </b-form-group>
                            <b-form-group label="Automatically confirm visits that clients modify" label-for="auto_confirm_modified" label-class="required">
                                <b-form-select id="auto_confirm_modified"
                                               :disabled="businessSettings.auto_confirm == 1 || businessSettings.allow_client_confirmations == 0"
                                               v-model="businessSettings.auto_confirm_modified"
                                >
                                    <option :value="0">No</option>
                                    <option :value="1">Yes</option>
                                </b-form-select>
                            </b-form-group>
                            <b-form-group
                                    label="Enable the 'Visit Summary with Pending Charges' email"
                                    label-for="shift_confirmation_email" label-class="required">
                                <b-form-select id="shift_confirmation_email"
                                               v-model="businessSettings.shift_confirmation_email"
                                >
                                    <option :value="0">No</option>
                                    <option :value="1">Yes</option>
                                </b-form-select>
                                <input-help :form="businessSettings" field="shift_confirmation_email" class="text-danger" text="Note: You will then need to enable this for each client on their profile."></input-help>
                            </b-form-group>
                            <div class="pl-5">
                                <b-form-group label="Include visits in progress" label-for="sce_shifts_in_progress">
                                    <b-form-select id="sce_shifts_in_progress"
                                                   :disabled="businessSettings.auto_confirm == 1 || true"
                                                   v-model="businessSettings.sce_shifts_in_progress"
                                    >
                                        <option :value="0">No</option>
                                        <option :value="1">Yes</option>
                                    </b-form-select>
                                </b-form-group>
                            </div>
                            <div>
                                <small class="form-text text-muted">Shift Confirmation Email Example:</small>
                                <img src="/images/shift-confirmation-email-example.png" class="email-example-img mb-1" />
                            </div>
                        </b-col>

                        <b-col lg="6">
                            <!-- Follow-up email is disabled and set to no (by watcher) when allow_client_confirmation == 0 -->
                            <b-form-group
                                    label="Send follow up email to client if total charge differs after modifying or adding visits"
                                    label-for="charge_diff_email" label-class="required">
                                <b-form-select id="charge_diff_email"
                                               disabled
                                               v-model="businessSettings.charge_diff_email"
                                >
                                    <option :value="0">No</option>
                                    <option :value="1">Yes</option>
                                </b-form-select>
                            </b-form-group>
                            <b-form-group
                                    label="Automatically append hours to visits in progress even after client confirms"
                                    label-for="auto_append_hours" label-class="required">
                                <b-form-select id="auto_append_hours"
                                               :disabled="businessSettings.auto_confirm == 1 || true"
                                               v-model="businessSettings.auto_append_hours"
                                >
                                    <option :value="0">No</option>
                                    <option :value="1">Yes</option>
                                </b-form-select>
                            </b-form-group>
                            <b-form-group label="Automatically confirm all visits if clients do not modify"
                                          label-for="auto_confirm_unmodified_shifts" label-class="required">
                                <b-form-select id="auto_confirm_unmodified_shifts"
                                               :disabled="businessSettings.auto_confirm == 1 || true"
                                               v-model="businessSettings.auto_confirm_unmodified_shifts"
                                >
                                    <option :value="0">No</option>
                                    <option :value="1">Yes</option>
                                </b-form-select>
                            </b-form-group>
                            <b-form-group
                                    label="Automatically confirm visits that are successfully verified via GPS or telephony"
                                    label-for="auto_confirm_verified_shifts" label-class="required">
                                <b-form-select id="auto_confirm_verified_shifts"
                                               :disabled="businessSettings.auto_confirm == 1"
                                               v-model="businessSettings.auto_confirm_verified_shifts"
                                >
                                    <option :value="0">No</option>
                                    <option :value="1">Yes</option>
                                </b-form-select>
                            </b-form-group>
                            <b-form-group label="Automatically confirm ALL visits" label-for="auto_confirm" label-class="required">
                                <b-form-select id="auto_confirm"
                                               v-model="businessSettings.auto_confirm"
                                >
                                    <option :value="0">No</option>
                                    <option :value="1">Yes</option>
                                </b-form-select>
                                <input-help :form="businessSettings" field="auto_confirm" text="Automatically confirm shifts that are clocked in on the app or telephony."></input-help>
                            </b-form-group>
                            <b-form-group label="Ask on Confirmation" label-for="ask_on_confirm" label-class="required">
                                <b-form-select id="ask_on_confirm"
                                               :disabled="businessSettings.auto_confirm == 1"
                                               v-model="businessSettings.ask_on_confirm"
                                >
                                    <option :value="0">No</option>
                                    <option :value="1">Yes</option>
                                </b-form-select>
                                <input-help :form="businessSettings" field="ask_on_confirm"
                                            text="Display a confirmation box before confirming or unconfirming a shift."></input-help>
                            </b-form-group>
                        </b-col>
                    </b-row>
                    <b-row>
                        <b-col>
                            <hr />
                            <b-btn @click="update" variant="info" size="lg">
                                Save Settings
                            </b-btn>
                        </b-col>
                    </b-row>
                </b-tab>
                <b-tab title="Sales People" href="#sales-people">
                    <business-salesperson-list :business-id="businessId"></business-salesperson-list>
                </b-tab>
                <b-tab title="Custom fields" href="#custom-fields">
                    <b-alert show><strong>Note:</strong> Changes here will affect all office locations.</b-alert>
                    <custom-field-list />
                </b-tab>
                <b-tab title="Deactivation Reasons" href="#deactivation-reasons">
                    <b-alert show><strong>Note:</strong> Changes here will affect all office locations.</b-alert>
                    <b-row>
                        <b-col lg="6">
                            <h3>Client Reason Codes</h3>
                            <deactivation-reason-manager type="client"></deactivation-reason-manager>
                        </b-col>
                        <b-col lg="6">
                            <h3>Caregiver Reason Codes</h3>
                            <deactivation-reason-manager type="caregiver"></deactivation-reason-manager>
                        </b-col>
                    </b-row>
                </b-tab>
                <b-tab title="Status Aliases" href="#status-aliases">
                    <business-status-alias-manager :business="this.business"></business-status-alias-manager>
                </b-tab>
                <b-tab title="Overtime" href="#overtime">
                    <business-overtime-settings :business="this.business"></business-overtime-settings>
                </b-tab>
                <b-tab title="Claims" href="#cliams">
                    <b-row>
                        <b-col lg="6">
                            <h4>HHAeXchange Credentials</h4>
                            <hr/>
                            <b-form-group label="Username" label-for="hha_username">
                                <b-form-input id="hha_username" v-model="businessSettings.hha_username"></b-form-input>
                                <input-help :form="businessSettings" field="hha_username" text="Enter your HHAeXchange provided SFTP username."></input-help>
                            </b-form-group>
                            <b-form-group label="Password" label-for="hha_password">
                                <b-form-input id="hha_password" v-model="businessSettings.hha_password"></b-form-input>
                                <input-help :form="businessSettings" field="hha_password" text="Enter your HHAeXchange provided SFTP password."></input-help>
                            </b-form-group>
                        </b-col>
                        <b-col lg="6">
                            <h4>Tellus Credentials</h4>
                            <hr/>
                            <b-form-group label="Username" label-for="tellus_username">
                                <b-form-input id="tellus_username" v-model="businessSettings.tellus_username"></b-form-input>
                                <input-help :form="businessSettings" field="tellus_username" text="Enter your Tellus provided API username."></input-help>
                            </b-form-group>
                            <b-form-group label="Password" label-for="tellus_password">
                                <b-form-input id="tellus_password" v-model="businessSettings.tellus_password"></b-form-input>
                                <input-help :form="businessSettings" field="tellus_password" text="Enter your Tellus provided API password."></input-help>
                            </b-form-group>
                        </b-col>
                    </b-row>
                    <b-row>
                        <b-col>
                            <hr />
                            <b-btn @click="update" variant="info" size="lg">
                                Save Settings
                            </b-btn>
                        </b-col>
                    </b-row>
                </b-tab>
            </b-tabs>
        </b-card>
    </div>
</template>

<script>
    import BusinessLocationSelect from "./BusinessLocationSelect";

    import {mapGetters, mapState, mapMutations} from 'vuex';

    export default {
        components: {BusinessLocationSelect},
        props: {},

        data() {
            return {
                businessId: "",
                businessSettings: new Form({}),
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
                tabIndex: 0,
            }
        },

        computed: {
            business() {
                return this.$store.getters.getBusiness(this.businessId) || ""
            },

            tabs() {
                if (this.business.type == 'agency') {
                    return ['#system', '#phone', '#medicaid', '#questions', '#payroll', '#shift-confirmations', '#custom-fields', '#deactivation-reasons', '#status-aliases', '#overtime', '#claims'];
                } else {
                    return ['#system', '#phone', '#medicaid', '#questions', '#shift-confirmations', '#custom-fields', '#deactivation-reasons', '#status-aliases', '#overtime', '#claims'];
                }
            },
        },

        mounted() {
            let index = this.tabs.findIndex(tab => tab === window.location.hash);
            if (index >= 0) {
                if (index > 3 && this.business.type != 'agency') {
                    index++;
                }
                this.tabIndex = index;
            }
        },

        methods: {
            ...mapMutations(['updateBusiness']),

            ...mapGetters(['defaultBusiness', 'getBusiness']),

            makeForm(business) {
                return new Form({
                    //logo: business.logo,
                    business_id: business.id,
                    scheduling: business.scheduling,
                    mileage_rate: business.mileage_rate,
                    calendar_default_view: business.calendar_default_view,
                    calendar_caregiver_filter: business.calendar_caregiver_filter,
                    calendar_remember_filters: business.calendar_remember_filters,
                    calendar_next_day_threshold: business.calendar_next_day_threshold,
                    phone1: business.phone1,
                    phone2: business.phone2,
                    address1: business.address1,
                    address2: business.address2,
                    city: business.city,
                    state: business.state,
                    zip: business.zip,
                    country: business.country,
                    timezone: business.timezone,
                    auto_confirm: business.auto_confirm,
                    ask_on_confirm: business.ask_on_confirm,
                    allows_manual_shifts: business.allows_manual_shifts,
                    location_exceptions: business.location_exceptions,
                    timesheet_exceptions: business.timesheet_exceptions,
                    shift_rounding_method: business.shift_rounding_method,
                    require_signatures: business.require_signatures,
                    co_mileage: business.co_mileage,
                    co_injuries: business.co_injuries,
                    co_comments: business.co_comments,
                    co_expenses: business.co_expenses,
                    co_issues: business.co_issues,
                    co_signature: business.co_signature,
                    ein: business.ein,
                    medicaid_id: business.medicaid_id,
                    medicaid_npi_number: business.medicaid_npi_number,
                    medicaid_npi_taxonomy: business.medicaid_npi_taxonomy,
                    allow_client_confirmations: business.allow_client_confirmations,
                    auto_confirm_modified: business.auto_confirm_modified,
                    shift_confirmation_email: business.shift_confirmation_email,
                    sce_shifts_in_progress: business.sce_shifts_in_progress,
                    charge_diff_email: business.charge_diff_email,
                    auto_append_hours: business.auto_append_hours,
                    auto_confirm_unmodified_shifts: business.auto_confirm_unmodified_shifts,
                    auto_confirm_verified_shifts: business.auto_confirm_verified_shifts,
                    enable_client_onboarding: business.enable_client_onboarding,
                    hha_username: business.hha_username,
                    hha_password: business.hha_password ? '********' : '',
                    tellus_username: business.tellus_username,
                    tellus_password: business.tellus_password ? '********' : '',
                });
            },

            async update() {
                const response = await
                this.businessSettings.put('/business/settings/' + this.business.id)
                    .then( ({ data }) => {
                        if (this.businessSettings.hha_password) {
                            this.businessSettings.hha_password = '********';
                        }
                        if (this.businessSettings.tellus_password) {
                            this.businessSettings.tellus_password = '********';
                        }
                        this.$store.commit('updateBusiness', data.data);
                    })
                    .catch(e => {});
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
            },

        },

        watch: {
            signatureOption() {
                this.updateSignatureValues()
            },

            business(business, oldBusiness) {
                console.dir(business);
                if (!oldBusiness && business) {
                    this.businessSettings = this.makeForm (business);
                    this.signatureOption = this.getSignatureOption (business);
                    return;
                }

                if (business.id !== oldBusiness.id) {
                    this.businessSettings = this.makeForm(business);
                    this.signatureOption = this.getSignatureOption(business);
                }
            },
            'businessSettings.allow_client_confirmations': function(value) {
                if (!value) {
                    this.businessSettings.shift_confirmation_email = 0;
                    this.businessSettings.charge_diff_email = 0;
                }
            },
        }
    }
</script>

<style scoped>
    .email-example-img {
        max-width: 100%;
        border: 1px solid #eee;
    }
</style>
