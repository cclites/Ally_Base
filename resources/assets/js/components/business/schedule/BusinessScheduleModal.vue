<template>
    <div>
        <b-modal id="businessScheduleModal"
                 :title="title"
                 class="modal-fit-more"
                 size="xl"
                 :no-close-on-backdrop="true"
                 v-model="scheduleModal"
        >
            <loading-card text="Loading details" v-show="isLoading"></loading-card>
            <b-card no-body v-if="!isLoading">
                <b-tabs card v-model="activeTab" ref="tabs">
                    <b-tab title="Shift Details" id="schedule-main">
                        <b-row>
                            <b-col sm="6">
                                <b-form-group label="Client" label-for="client_id">
                                    <b-form-select
                                            id="client_id"
                                            name="client_id"
                                            v-model="form.client_id"
                                            @input="changedClient(form.client_id)"
                                            required
                                    >
                                        <option value="">--Select a Client--</option>
                                        <option v-for="item in clients" :value="item.id" :key="item.id">{{ item.nameLastFirst }}</option>
                                    </b-form-select>
                                    <input-help :form="form" field="client_id" text="Select the client for this schedule." />
                                </b-form-group>
                            </b-col>
                            <b-col sm="6">
                                <b-form-group>
                                    <div class="float-right">
                                        <b-btn variant="link" size="sm" @click="toggleCaregivers()" class="p-0">
                                            {{ toggleCaregiversLabel }}
                                        </b-btn> |
                                        <b-btn variant="link" size="sm" @click="openCareMatchTab()" class="p-0">
                                            Find Caregivers
                                        </b-btn>
                                    </div>
                                    <label class="col-form-label pt-0" for="caregiver_id">Caregiver</label>
                                    <b-form-select
                                            id="caregiver_id"
                                            name="caregiver_id"
                                            v-model="form.caregiver_id"
                                            @input="changedCaregiver(form.caregiver_id)"
                                    >
                                        <option value="">--Not Assigned--</option>
                                        <option v-for="caregiver in caregivers" :value="caregiver.id" :key="caregiver.id">{{ caregiver.nameLastFirst }}</option>
                                    </b-form-select>
                                    <small v-if="caregiverAssignmentMode" class="form-text text-muted">
                                        <span class="text-warning">Caregivers that are not currently assigned to the client will be automatically assigned.</span>
                                    </small>
                                    <input-help v-else :form="form" field="caregiver_id" text="Select the caregiver for this schedule." />
                                </b-form-group>
                            </b-col>
                        </b-row>

                        <b-row>
                            <b-col lg="12" class="pb-2">
                                <strong>Scheduled Time & Service Needs</strong>
                            </b-col>
                            <b-col>
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                        <tr>
                                            <th style="width: 20%;">Start Date</th>
                                            <th style="width: 20%;">End Date</th>
                                            <th>Start Time</th>
                                            <th>End Time</th>
                                            <th>Hours</th>
                                            <th>Service Needs (ADLs)</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr>
                                            <td>
                                                <date-picker v-model="startDate" @input="changedStartDate(startDate)" />
                                            </td>
                                            <td>
                                                <date-picker v-model="firstShiftEndDate" disabled />
                                            </td>
                                            <td>
                                                <time-picker name="startTime" v-model="startTime" @input="changedStartTime(startTime)" />
                                            </td>
                                            <td>
                                                <time-picker name="endTime" v-model="endTime" @input="changedEndTime(endTime)" />
                                            </td>
                                            <td class="text-only">
                                                {{ scheduledHours }}
                                            </td>
                                            <td>
                                                <b-form-select name="care_plan_id" v-model="form.care_plan_id">
                                                    <option value="">--None--</option>
                                                    <option v-for="item in care_plans" :value="item.id" :key="item.id">{{ item.name }}</option>
                                                </b-form-select>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <input-help :form="form" field="starts_at" text="" />
                                <input-help :form="form" field="duration" text="" />
                            </b-col>
                        </b-row>

                        <b-row class="mt-2">
                            <b-col lg="12">
                                <strong>Scheduled Billing</strong>
                                <b-form-group class="pt-2 mb-0">
                                    <b-form-radio-group v-model="billingType">
                                        <b-form-radio value="hourly">Hourly</b-form-radio>
                                        <b-form-radio value="fixed">Fixed Rate</b-form-radio>
                                        <b-form-radio value="services">Service Breakout</b-form-radio>
                                    </b-form-radio-group>
                                </b-form-group>
                            </b-col>
                        </b-row>

                        <b-row>
                            <b-col>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-fit-more">
                                        <thead>
                                        <tr>
                                            <th>Service</th>
                                            <th>Hours Type</th>
                                            <th width="10%">Hours</th>
                                            <th width="13%">Caregiver Rate</th>
                                            <th>Registry Fee</th>
                                            <th>Ally Fee</th>
                                            <th width="12%">Total Rate</th>
                                            <th>Payer</th>
                                            <th v-if="allowQuickbooksMapping">Quickbooks Mapping</th>
                                            <th class="service-actions"></th>
                                        </tr>
                                        </thead>
                                        <tbody>

                                        <!-- Hourly / Fixed -->
                                        <tr v-if="billingType === 'hourly' || billingType === 'fixed'">
                                            <td>
                                                <b-form-select v-model="form.service_id" class="services" @input="changedService(form, form.service_id)">
                                                    <option v-for="service in services" :value="service.id">{{ service.name }} {{ service.code }}</option>
                                                </b-form-select>
                                            </td>
                                            <td>
                                                <b-form-select id="hours_type" v-model="form.hours_type" name="hours_type" @change="(x) => onChangeHoursType(x, this.form.hours_type)">
                                                    <option value="default">REG</option>
                                                    <option value="holiday">HOL</option>
                                                    <option value="overtime">OT</option>
                                                </b-form-select>
                                            </td>
                                            <td class="text-only">
                                                {{ billingType === 'hourly' ? scheduledHours : 'Fixed' }}
                                            </td>
                                            <td class="text-only" v-if="defaultRates">
                                                {{ numberFormat(form.default_rates.caregiver_rate) }}
                                            </td>
                                            <td v-else>
                                                <b-form-input
                                                    name="caregiver_rate"
                                                    type="number"
                                                    step="0.01"
                                                    min="0"
                                                    max="999.99"
                                                    v-model="form.caregiver_rate"
                                                    @change="updateProviderRates(form)"
                                                    class="money-input"
                                                />
                                            </td>
                                            <td class="text-only"  v-if="defaultRates">
                                                {{ numberFormat(form.default_rates.provider_fee) }}
                                            </td>
                                            <td v-else>
                                                <b-form-input
                                                        name="provider_fee"
                                                        type="number"
                                                        step="0.01"
                                                        min="0"
                                                        max="999.99"
                                                        v-model="form.provider_fee"
                                                        @change="updateClientRates(form)"
                                                        class="money-input"
                                                />
                                            </td>
                                            <td class="text-only">
                                                <span v-if="defaultRates">{{ numberFormat(form.default_rates.ally_fee) }}</span>
                                                <span v-else>{{ numberFormat(form.ally_fee) }}</span>
                                            </td>
                                            <td class="text-only" v-if="defaultRates">
                                                {{ numberFormat(form.default_rates.client_rate) }}
                                            </td>
                                            <td v-else>
                                                <b-form-input
                                                        name="client_rate"
                                                        type="number"
                                                        step="0.01"
                                                        min="0"
                                                        max="999.99"
                                                        v-model="form.client_rate"
                                                        @change="updateProviderRates(form)"
                                                        class="money-input"
                                                />
                                            </td>
                                            <td :colspan="allowQuickbooksMapping ? 1 : 2">
                                                <b-form-select v-model="form.payer_id" class="payers" @input="changedPayer(form, form.payer_id)">
                                                    <option :value="null">(Auto)</option>
                                                    <option v-for="payer in clientPayers" :value="payer.id">{{ payer.name }}</option>
                                                </b-form-select>
                                            </td>
                                            <td colspan="2" v-if="allowQuickbooksMapping">
                                                <b-form-select v-model="form.quickbooks_service_id" :disabled="disableQuickbooksMapping">
                                                    <option value="">--None--</option>
                                                    <option v-for="item in quickbooksServices" :value="item.id" :key="item.id">{{ item.name }}</option>
                                                </b-form-select>
                                            </td>
                                        </tr>

                                        <!-- Service Breakout -->
                                        <tr v-if="billingType === 'services'" v-for="(service,index) in form.services">
                                            <td>
                                                <b-form-select v-model="service.service_id" class="services" @input="changedService(service, service.service_id)">
                                                    <option v-for="s in services" :value="s.id">{{ s.name }} {{ s.code }}</option>
                                                </b-form-select>
                                            </td>
                                            <td>
                                                <b-form-select id="hours_type" v-model="service.hours_type" name="hours_type" style="min-width: 80px;" @change="(x) => onChangeServiceHoursType(x, service.hours_type, index)">
                                                    <option value="default">REG</option>
                                                    <option value="holiday">HOL</option>
                                                    <option value="overtime">OT</option>
                                                </b-form-select>
                                            </td>
                                            <td>
                                                <b-form-input
                                                    name="duration"
                                                    type="number"
                                                    step="0.01"
                                                    min="0"
                                                    max="999.99"
                                                    v-model="service.duration"
                                                    @change="(val) => service.duration = parseFloat(val).toFixed(2)" />
                                            </td>
                                            <td class="text-only" v-if="defaultRates">
                                                {{ numberFormat(service.default_rates.caregiver_rate) }}
                                            </td>
                                            <td v-else>
                                                <b-form-input
                                                        name="caregiver_rate"
                                                        type="number"
                                                        step="0.01"
                                                        min="0"
                                                        max="999.99"
                                                        v-model="service.caregiver_rate"
                                                        @change="updateProviderRates(service)"
                                                        class="money-input"
                                                />
                                            </td>
                                            <td class="text-only" v-if="defaultRates">
                                                {{ numberFormat(service.default_rates.provider_fee) }}
                                            </td>
                                            <td v-else>
                                                <b-form-input
                                                        name="provider_fee"
                                                        type="number"
                                                        step="0.01"
                                                        min="0"
                                                        max="999.99"
                                                        v-model="service.provider_fee"
                                                        @change="updateClientRates(service)"
                                                        class="money-input"
                                                />
                                            </td>
                                            <td class="text-only">
                                                <span v-if="defaultRates">{{ numberFormat(service.default_rates.ally_fee) }}</span>
                                                <span v-else>{{ numberFormat(service.ally_fee) }}</span>
                                            </td>
                                            <td class="text-only" v-if="defaultRates">
                                                {{ numberFormat(service.default_rates.client_rate) }}
                                            </td>
                                            <td v-else>
                                                <b-form-input
                                                        name="client_rate"
                                                        type="number"
                                                        step="0.01"
                                                        min="0"
                                                        max="999.99"
                                                        v-model="service.client_rate"
                                                        @change="updateProviderRates(service)"
                                                        class="money-input"
                                                />
                                            </td>
                                            <td>
                                                <b-form-select v-model="service.payer_id" class="payers" @input="changedPayer(service, service.payer_id)">
                                                    <option :value="null">(Auto)</option>
                                                    <option v-for="payer in clientPayers" :value="payer.id">{{ payer.name }}</option>
                                                </b-form-select>
                                            </td>
                                            <td v-if="allowQuickbooksMapping">
                                                <b-form-select v-model="service.quickbooks_service_id" :disabled="disableQuickbooksMapping">
                                                    <option value="">--None--</option>
                                                    <option v-for="item in quickbooksServices" :value="item.id" :key="item.id">{{ item.name }}</option>
                                                </b-form-select>
                                            </td>
                                            <td class="service-actions text-nowrap">
                                                <b-btn size="xs" @click="removeService(index)" v-if="form.services.length > 1">
                                                    <i class="fa fa-times"></i>
                                                </b-btn>
                                                <b-btn size="xs" variant="success" style="background-color: green;" @click="addService()" v-if="index === form.services.length - 1">
                                                    <i class="fa fa-plus"></i>
                                                </b-btn>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div v-if="billingType === 'services' && serviceHours != scheduledHours" class="alert alert-warning">
                                    Warning: The scheduled hours ({{ numberFormat(scheduledHours) }}) do not match the broken out service hours ({{ numberFormat(serviceHours) }}).
                                </div>
                                <b-alert v-if="isUsingOvertime" variant="warning" show>
                                    Note: Because OT/HOL is selected, the rates have been re-calculated to match your settings.
                                </b-alert>
                                <div v-if="warnings && warnings.length">
                                    <b-alert v-for="(warning, index) in warnings" :key="index" variant="warning" show>
                                        <strong>{{ warning.label }}:</strong> {{ warning.description }}
                                    </b-alert>
                                </div>
                            </b-col>
                        </b-row>
                        <b-row>
                            <b-col lg="12">
                                <b-alert v-if="caregiverAssignmentMode" show variant="info">
                                    <strong>Note:</strong> Because you are assigning a new Caregiver, this will automatically create new default rates using the services/payers above.
                                </b-alert>
                            </b-col>
                            <b-col lg="6">
                                <label>
                                    <!-- Create a dummy checkbox if we are in assign cg mode -->
                                    <b-form-checkbox v-if="caregiverAssignmentMode" :checked="true" :disabled="true">
                                        Use Default Rates from Caregivers &amp; Rates Tab of Client Profile
                                    </b-form-checkbox>
                                    <b-form-checkbox v-show="!caregiverAssignmentMode" v-model="defaultRates">
                                        Use Default Rates from Caregivers &amp; Rates Tab of Client Profile
                                    </b-form-checkbox>
                                    <a v-if="form.client_id" :href="`/business/clients/${form.client_id}#rates`" target="_blank">Manage Client Rates</a>
                                </label>
                            </b-col>
                            <!--<b-col lg="6" class="text-right">-->
                                <!--<small>* Provider Fee &amp; Ally Fee are estimated.  (Payment Type: {{ paymentType }} {{ displayAllyPct }}%)</small>-->
                            <!--</b-col>-->
                        </b-row>
                    </b-tab>
                    <b-tab title="Recurrence" id="schedule-recurrence" v-if="!schedule.id">
                        <b-row>
                            <b-col lg="12">
                                <b-form-group label="Recurring Period" label-for="interval_type">
                                    <b-form-select
                                            id="interval_type"
                                            name="interval_type"
                                            v-model="form.interval_type"
                                    >
                                        <option value="">Single Shift Only</option>
                                        <option value="weekly">Weekly</option>
                                        <option value="biweekly">Bi-weekly</option>
                                        <option value="monthly">Monthly</option>
                                    </b-form-select>
                                    <input-help :form="form" field="interval_type" text="Select how often the schedule repeats." />
                                </b-form-group>
                                <div v-if="form.interval_type">
                                    <div class="form-check" v-show="form.interval_type === 'weekly' || form.interval_type === 'biweekly'">
                                        <input-help :form="form" field="bydays" text="Select the days of the week below." />
                                        <label class="custom-control custom-checkbox" v-for="(item, index) in daysOfWeek" :key="item">
                                            <input type="checkbox" class="custom-control-input" name="bydays[]" v-model="form.bydays" :value="item">
                                            <span class="custom-control-indicator"></span>
                                            <span class="custom-control-description">{{ index }}</span>
                                        </label>
                                    </div>
                                    <p v-show="form.interval_type === 'monthly'">
                                        The schedule will repeat every month on the {{ dayOfMonth(form.start_date) }}.
                                    </p>
                                    <b-form-group label="End date" label-for="endDate">
                                        <date-picker v-model="endDate" />
                                        <input-help :form="form" field="recurring_end_date" text="Repeat the schedule until this date.  If left blank, the schedule will repeat for 2 years." />
                                    </b-form-group>
                                </div>
                            </b-col>
                        </b-row>
                    </b-tab>
                    <b-tab title="Notes" id="schedule-notes">
                        <b-row>
                            <b-col lg="12">
                                <b-form-group label="Schedule Status" label-for="status" v-if="schedule.id">
                                    <b-form-select
                                            id="status"
                                            name="status"
                                            v-model="form.status"
                                    >
                                        <option value="OK">No Status</option>
                                        <option value="ATTENTION_REQUIRED">Attention Required</option>
                                        <option value="CLIENT_CANCELED">Client Canceled</option>
                                        <option value="CAREGIVER_CANCELED">Caregiver Canceled</option>
                                        <option value="CAREGIVER_NOSHOW">Caregiver No Show</option>
                                        <option value="OPEN_SHIFT">Open Shift</option>
                                    </b-form-select>
                                </b-form-group>
                                <b-form-group label="Add a note for the Caregiver to see" label-for="notes">
                                    <b-form-textarea
                                            id="notes"
                                            name="notes"
                                            :rows="6"
                                            v-model="form.notes"
                                    >
                                    </b-form-textarea>
                                    <input-help :form="form" field="notes" text="Note will be visible to Caregiver when clocking in on the Ally mobile app." />
                                </b-form-group>
                            </b-col>
                        </b-row>
                    </b-tab>
                    <b-tab title="Find a Caregiver with CareMatch" button-id="care-match-tab">
                        <business-care-match :clients="clients" :schedule="careMatchSchedule">
                            <template scope="row">
                                <b-button size="sm" variant="info" @click="selectCaregiver(row.item.id)">Select Caregiver</b-button>
                            </template>
                        </business-care-match>
                    </b-tab>
                </b-tabs>
            </b-card>

            <div slot="modal-footer" v-if="!isLoading">
                <b-btn variant="info" @click="submitForm()" :disabled="submitting">
                    <i class="fa fa-spinner fa-spin" v-show="submitting"></i>
                    <i class="fa fa-save" v-show="!submitting"></i>
                    {{ submitText }}
                </b-btn>
                <b-btn variant="primary" @click="copySchedule()" v-show="schedule.id" class="mr-auto"><i class="fa fa-copy"></i> Copy</b-btn>
                <b-btn v-show="schedule.clocked_in_shift" variant="warning" @click="clockOut()">Clock Out Shift</b-btn>
                <b-btn variant="danger" @click="deleteSchedule()" v-show="schedule.id" class="mr-auto"><i class="fa fa-times"></i> Delete</b-btn>
                <b-btn variant="default" @click="scheduleModal=false">Close</b-btn>
            </div>
        </b-modal>
        <confirmation-modal v-model="maxHoursWarning"
                            title="Confirm Service Auth Override"
                            @confirm="overrideMaxHours()"
                            @cancel="scheduleModal=false"
        >
            <h4>This will put the client over the maximum weekly hours.  Are you sure you want to do this?</h4>
        </confirmation-modal>
        <schedule-group-modal v-if="selectedSchedule.group_data"
                              :group-data="selectedSchedule.group_data"
                              :weekday-int="scheduledWeekdayInt"
                              :day-change="scheduledWeekdayInt !== currentWeekdayInt"
                              v-model="groupModal"
                              @submit="submitWithGroup"
        />
    </div>
</template>

<script>
    import FormatsDates from "../../../mixins/FormatsDates";
    import FormatsNumbers from "../../../mixins/FormatsNumbers";
    import RateCodes from "../../../mixins/RateCodes";
    import RateFactory from "../../../classes/RateFactory";
    import ConfirmationModal from "../../modals/ConfirmationModal";
    import ShiftServices from "../../../mixins/ShiftServices";
    import ScheduleGroupModal from "../../modals/ScheduleGroupModal";
    import { mapGetters } from 'vuex';

    export default {
        components: {ScheduleGroupModal, ConfirmationModal},
        mixins: [FormatsNumbers, RateCodes, ShiftServices, FormatsDates],

        props: {
            model: Boolean,
            passClients: {
                type: Array,
                required: true,
            },
            passCaregivers: {
                type: Array,
            },
            selectedSchedule: {
                type: Object,
                default() {
                    return {};
                }
            },
        },

        data() {
            return {
                activeTab: 0,
                submitting: false,
                startDate: "",
                startTime: "",
                endTime: "",
                endDate: "",
                scheduleModal: this.model,
                form: new Form(),
                copiedSchedule: {},
                allyPct: 0.05,
                paymentType: 'NONE',
                clientCaregivers: [],
                clientCaregiversLoaded: false,
                cgMode: 'client',
                care_plans: [],
                daysOfWeek: {
                    'Sunday': 'su',
                    'Monday': 'mo',
                    'Tuesday': 'tu',
                    'Wednesday': 'we',
                    'Thursday': 'th',
                    'Friday': 'fr',
                    'Saturday': 'sa',
                },
                specialHoursChange: false,
                maxHoursWarning: false,
                allCaregivers: this.passCaregivers,
                groupModal: false,
                warnings: [],
                loadingQuickbooksConfig: false,
            }
        },

        mounted() {
            this.fetchServices(); // from ShiftServices mixin
            this.loadClientData();
            this.loadAllCaregivers();
            this.fetchRateCodes();
        },

        computed: {
            ...mapGetters({
                quickbooksServices: 'quickbooks/services',
                quickbooksBusiness: 'quickbooks/businessId',
                quickbooksIsAuthorized: 'quickbooks/isAuthorized',
                quickbooksAllowMapping: 'quickbooks/mapServiceFromShifts',
            }),

            caregiverAssignmentMode() {
                return this.cgMode == 'all' && this.clientCaregiversLoaded && this.form.caregiver_id && ! this.selectedCaregiver.id;
            },

            selectedCaregiver() {
                if (this.form.caregiver_id) {
                    for(let index in this.clientCaregivers) {
                        let caregiver = this.clientCaregivers[index];
                        if (caregiver.id == this.form.caregiver_id) {
                            return caregiver;
                        }
                    }
                }
                return {
                    pivot: {}
                };
            },

            selectedClient() {
                return this.form.client_id ? this.clients.find(client => client.id == this.form.client_id) || {} : {};
            },

            business() {
                return this.selectedClient.business_id ? this.$store.getters.getBusiness(this.selectedClient.business_id) : {};
            },

            title() {
                if (this.copiedSchedule.starts_at) {
                    return 'Copying Schedule';
                }
                if (this.selectedSchedule.id) {
                    return 'Editing a Scheduled Shift';
                }
                return 'Schedule Shift';
            },

            submitText() {
                if (this.selectedSchedule.id) {
                    return 'Save';
                }
                return 'Create Schedule';
            },

            isLoading() {
                return _.isEmpty(this.selectedSchedule);
            },

            schedule() {
                if (this.copiedSchedule.starts_at) return this.copiedSchedule;
                return this.selectedSchedule;
            },

            displayAllyPct() {
                return (parseFloat(this.allyPct) * 100).toFixed(2);
            },

            caregivers() {
                if (! this.form.client_id || this.cgMode === 'all') {
                    return this.passCaregivers || this.allCaregivers;
                }
                return this.clientCaregivers;
            },

            clients() {
                return this.passClients;
            },

            toggleCaregiversLabel() {
                if (this.cgMode === 'all') {
                    return "Show only Client's"
                }
                return 'Show All';
            },

            rateType() {
                return (this.form.fixed_rates === 1) ? 'Fixed' : 'Hourly';
            },

            firstShiftEndDate() {
                let duration = this.getDuration();
                return moment(this.startDate + ' ' + this.startTime, 'MM/DD/YYYY HH:mm')
                    .add(duration, 'minutes')
                    .format('MM/DD/YYYY');
            },

            scheduledHours() {
                if (this.form.duration) {
                    return this.numberFormat(parseInt(this.form.duration) / 60);
                }
                return 0;
            },

            careMatchSchedule() {
                return {
                    starts_at: moment(this.startDate + ' ' + this.startTime, 'MM/DD/YYYY HH:mm').format('YYYY-MM-DD HH:mm:ss'),
                    duration: this.form.duration,
                    client_id: this.form.client_id,
                }
            },

            isPast() {
                if (this.selectedSchedule.id) {
                    return moment(this.selectedSchedule.starts_at).isBefore(moment());
                } else {
                    return moment(this.getStartsAt()).isBefore(moment());
                }
            },
            scheduledWeekdayInt() {
                return this.selectedSchedule ? moment(this.selectedSchedule.starts_at).day() : 0;
            },

            currentWeekdayInt() {
                return this.startDate ? moment(this.startDate).day() : 0;
            },

            allowQuickbooksMapping() {
                return this.quickbooksAllowMapping && this.quickbooksIsAuthorized;
            },

            disableQuickbooksMapping() {
                return !this.business || this.loadingQuickbooksConfig;
            },
        },

        methods: {

            changedSchedule(schedule) {
                // initiated from watcher
                this.makeForm(schedule);
                this.changedClient(schedule.client_id);
            },

            changedClient(clientId) {
                if (!clientId || this.client_id === clientId) {
                    return;
                }
                this.loadCaregivers(clientId);
                this.loadAllyPctFromClient(clientId);
                this.loadCarePlans(clientId);
                this.loadClientRates(clientId);
                this.loadClientPayers(clientId);
            },

            changedCaregiver(caregiverId) {
                this.fetchAllRates();

                // Automatically reset the schedule status when it is a
                // no show or open shift and a new caregiver is set otherwise
                // saving the schedule will clear the caregiver_id because of
                // its status.
                if (caregiverId && (this.form.status == 'CAREGIVER_NOSHOW' || this.form.status == 'OPEN_SHIFT')) {
                    this.form.status = 'OK';
                }
            },

            checkForWarnings: _.debounce((vm) => {
                console.log('check warnings: ', vm);
                let form = new Form({
                    caregiver: vm.form.caregiver_id ? vm.form.caregiver_id : '',
                    client: vm.form.client_id ? vm.form.client_id : '',
                    duration: vm.getDuration(),
                    starts_at: vm.getStartsAt(),
                    id: vm.schedule.id ? vm.schedule.id : '',
                    payer_id: vm.form.payer_id,
                    service_id: vm.form.service_id,
                    services: vm.form.services,
                });

                if (! form.caregiver && ! form.client) {
                    // skip warnings if client and cg not set
                    return;
                }

                form.alertOnResponse = false;
                form.post('/business/schedule/warnings')
                    .then( ({ data }) => {
                        vm.warnings = data;
                    })
                    .catch(e => {})
            }, 350),

            changedStartDate(startDate) {
                this.fetchAllRates();
            },

            changedStartTime(startTime) {
                this.form.duration = this.getDuration();
            },

            changedEndTime(endTime) {
                this.form.duration = this.getDuration();
            },

            changedPayer(service, payerId) {
                this.fetchDefaultRate(service);
            },

            changedService(service, serviceId) {
                this.fetchDefaultRate(service);
            },

            changedBillingType(type) {
                // initiated from watcher
                // pass to mixin
                this.handleChangedBillingType(this.form, type);
            },

            changedDefaultRates(value) {
                // initiated from watcher
                // pass to mixin
                this.handleChangedDefaultRates(this.form, value);
            },

            selectCaregiver(id) {
                this.cgMode = 'all';
                this.form.caregiver_id = id;
                this.activeTab = 0;
            },

            openCareMatchTab() {
                const tabs = this.$refs.tabs.tabs;
                for (let i = 0; i < tabs.length; i ++) {
                    if (tabs[i].title.toString().match(/CareMatch/)) {
                        this.activeTab = i;
                        break;
                    }
                }
            },

            makeForm(schedule) {
                if (!schedule) schedule = this.schedule;

                this.billingType = schedule.fixed_rates ? 'fixed' : 'hourly';
                this.defaultRates = this.caregiverAssignmentMode ? false : schedule.client_rate == null;
                console.log('init defaultRates: ', this.defaultRates, schedule.client_rate);
                this.warnings = [];

                // Initialize form
                this.$nextTick(() => {
                    this.form = new Form({
                        'starts_at': schedule.starts_at || "",
                        'duration': schedule.duration || 0,
                        'caregiver_id': schedule.caregiver_id || "",
                        'client_id': schedule.client_id || "",
                        'fixed_rates': schedule.fixed_rates ? 1 : 0,
                        'caregiver_rate': schedule.caregiver_rate || null,
                        'caregiver_rate_id': schedule.caregiver_rate_id || "",
                        'client_rate': schedule.client_rate || null,
                        'client_rate_id': schedule.client_rate_id || "",
                        'notes': schedule.notes || "",
                        'hours_type': schedule.hours_type || "default",
                        'overtime_duration': schedule.overtime_duration || 0,
                        'care_plan_id': schedule.care_plan_id || '',
                        'status': schedule.status || 'OK',
                        'service_id': schedule.service_id || this.defaultService.id,
                        'payer_id': schedule.payer_id == 0 ? 0 : schedule.payer_id || null,
                        'interval_type': "",
                        'recurring_end_date': "",
                        'bydays': [],
                        'services': [],
                        'provider_fee': null,
                        'ally_fee': null,
                        'group_update': null,
                        'default_rates': {
                            'client_rate': null,
                            'caregiver_rate': null,
                            'provider_fee': null,
                            'ally_fee': null,
                        },
                        'quickbooks_service_id': '',
                    });
                    this.recalculateRates(this.form, this.form.client_rate, this.form.caregiver_rate);
                    this.initServicesFromObject(schedule);
                    this.setDateTimeFromSchedule(schedule);
                });
            },

            setDateTimeFromSchedule(schedule) {
                let start = moment(schedule.starts_at, 'YYYY-MM-DD HH:mm:ss');
                this.startDate = start.format('MM/DD/YYYY');
                this.startTime = (start._ambigTime) ? '09:00' : start.format('HH:mm');
                let end = moment(start).add(schedule.duration || 60, 'minutes');
                this.endTime = (end._ambigTime) ? '10:00' : end.format('HH:mm');
            },

            submitWithGroup(groupUpdate)
            {
                return this.submitForm(groupUpdate);
            },

            submitForm(groupUpdate = null) {
                if (this.isPast) {
                    if (! confirm('Modifying past schedules will NOT change the shift history or billing.  Continue?')) {
                        return;
                    }
                }

                if (this.selectedSchedule.group_id && !groupUpdate) {
                    this.groupModal = true;
                    return;
                } else {
                    this.form.group_update = groupUpdate;
                }

                this.submitting = true;

                if (this.form.hours_type !== 'default') {
                    // Temporarily: Set overtime duration to duration
                    this.form.overtime_duration = this.duration;
                }

                if (!this.endDate) {
                    // Set end date to 2 years from now if empty
                    this.endDate = moment().add(2, 'years').format('MM/DD/YYYY');
                }

                // Fill/format form values
                this.form.duration = this.getDuration();
                this.form.starts_at = this.getStartsAt();
                this.form.recurring_end_date = moment(this.endDate + ' ' + this.startTime, 'MM/DD/YYYY HH:mm').add(1, 'minutes').format();

                // Finalize form billing type
                if (this.billingType === 'services') {
                    this.form.service_id = null;
                    this.form.payer_id = null;
                    this.form.fixed_rates = false;
                } else {
                    this.form.services = [];
                    this.form.fixed_rates = (this.billingType === 'fixed');
                }

                // Submit form
                let url = '/business/schedule';
                let method = 'post';
                if (this.schedule.id) {
                    method = 'patch';
                    url = url + '/' + this.schedule.id;
                }
                this.form.hideErrorsFor(449).submit(method, url)
                    .then(response => {
                        this.refreshEvents();
                        this.submitting = false;
                    })
                    .catch(error => {
                        this.handleErrors(error);
                        this.submitting = false;
                    });
            },

            copySchedule() {
                if (this.selectedSchedule.id) {
                    this.copiedSchedule = Object.assign({}, this.selectedSchedule);
                    Vue.delete(this.copiedSchedule, 'id');
                    this.changedSchedule(this.copiedSchedule);
                }
            },

            clockOut() {
                this.scheduleModal = false;
                this.$emit('clock-out');
            },

            deleteSchedule() {
                let confirmMessage = 'Are you sure you wish to delete this scheduled shift?';
                if (this.isPast) {
                    confirmMessage = "Are you sure you wish to delete this past entry?\nNote: Modifying past schedules will NOT change the shift history or billing.";
                }
                if (this.schedule.id && confirm(confirmMessage)) {
                    let form = new Form();
                    form.submit('delete', '/business/schedule/' + this.schedule.id)
                        .then(response => {
                            this.refreshEvents();
                        });
                }
            },

            dayOfMonth(date) {
                return moment(date).format('Do');
            },

            loadAllyPctFromClient(client_id) {
                if (!client_id) return;
                axios.get('/business/clients/' + client_id + '/payment_type').then(response => {
                    this.allyPct = response.data.percentage_fee;
                    this.paymentType = response.data.payment_type;
                });
            },

            loadCaregivers(clientId) {
                this.clientCaregiversLoaded = false;
                if (clientId) {
                    axios.get('/business/clients/' + clientId + '/caregivers')
                        .then(response => {
                            this.clientCaregivers = response.data;
                        })
                        .finally(() => {
                            this.clientCaregiversLoaded = true;
                        });
                }
            },

            loadClientData() {
                if (this.client_id) {
                    // Load caregivers and ally pct immediately
                    this.loadCaregivers(this.client_id);
                    this.loadAllyPctFromClient(this.client_id);
                    this.loadClientRates(this.client_id);
                    this.loadClientPayers(this.client_id);
                }
            },

            async loadAllCaregivers() {
                if (!this.passCaregivers) {
                    const response = await axios.get(`/business/caregivers?json=1`);
                    this.allCaregivers = response.data;
                }
            },

            getDuration() {
                if (this.endTime && this.startTime) {
                    if (this.startTime === this.endTime) {
                        return 1440; // have 12:00am to 12:00am = 24 hours
                    }
                    let start = moment(this.startDate + ' ' + this.startTime, 'MM/DD/YYYY HH:mm');
                    let end = moment(this.startDate + ' ' + this.endTime, 'MM/DD/YYYY HH:mm');
                    if (start && end) {
                        if (end.isBefore(start)) {
                            end = end.add(1, 'days');
                        }
                        let diff = end.diff(start, 'minutes');
                        if (diff) {
                            return parseInt(diff);
                        }
                    }
                }
                return null;
            },

            getStartsAt() {
                if (this.startDate && this.startTime) {
                    return moment(this.startDate + ' ' + this.startTime, 'MM/DD/YYYY HH:mm').format();
                }
                return null;
            },

            refreshEvents() {
                this.$emit('refresh-events');
                this.scheduleModal = false;
            },

            overrideMaxHours() {
                let data = this.form.data();
                data.override_max_hours = 1;
                this.form = new Form(data);
                this.submitForm();
            },

            handleErrors(error) {
                if (error.response) {
                    switch(error.response.status) {
                        case 449:
                            this.maxHoursWarning = true;
                            break;
                    }
                }
            },

            resetTabs() {
                this.activeTab = 1;
                this.$nextTick(function() {
                    // (fix for tabs within tabs)
                    this.activeTab = 0;
                });
            },

            loadCarePlans(client_id, old_val) {
                if (this.form.care_plan_id && old_val) {
                    this.form.care_plan_id = '';
                }
                let index = this.clients.findIndex(item => item.id == client_id);
                if (index > -1) {
                    this.care_plans = this.clients[index].care_plans;
                    return;
                }
                this.care_plans = [];
            },

            toggleCaregivers() {
                this.cgMode = this.cgMode === 'all' ? 'client' : 'all';
            },

            onChangeServiceHoursType(newVal, oldVal, serviceIndex) {
                let service = this.form.services[serviceIndex];
                if (!service) {
                    return;
                }

                // Use nextTick here so that you can properly get the oldVal using this
                // function on the @change event, but utilize the updated service
                // object that will reflect the new hours_type value.
                this.$nextTick(() => {
                    if (this.defaultRates) {
                        this.fetchDefaultRate(service);
                    } else {
                        this.handleChangedHoursType(service, newVal, oldVal);
                    }
                });
            },

            onChangeHoursType(newVal, oldVal) {
                this.handleChangedHoursType(this.form, newVal, oldVal);
            },
        },

        watch: {
            form: {
                handler(obj){
                    this.checkForWarnings(this);
                },
                deep: true
            },

            'form.hours_type': function(newVal, oldVal) {
                if (! oldVal || newVal == oldVal) {
                    return;
                }

                if (this.defaultRates) {
                    // re-load the default rates and will automatically
                    // calculate any OT/HOL hours.
                    this.handleChangedDefaultRates(this.form, this.defaultRates);
                }
            },

            model(val) {
                // Update local modal bool
                this.scheduleModal = val;
            },

            selectedSchedule(val) {
                // Force back to first tab
                this.resetTabs();

                // Clear copied values
                this.copiedSchedule = {};

                // Re-create the form object
                this.changedSchedule(val);

                // Use cg all mode if an caregiver is pre-selected
                if (this.schedule.caregiver_id) {
                    this.cgMode = 'all';
                } else {
                    this.cgMode = 'client';
                }
            },

            scheduleModal(val) {
                this.createType = null;
                this.$emit('update:model', val);
                if (val) {
                    this.loadClientData();
                }
            },

            allyPct() {
                this.recalculateAllRates(this.form)
            },

            // Watch if the business changes and refresh the current quickbooks settings.
            async business(newVal, oldVal) {
                if (newVal && newVal.id != oldVal.id) {
                    this.loadingQuickbooksConfig = true;
                    await this.$store.dispatch('quickbooks/fetchConfig', newVal.id);
                    await this.$store.dispatch('quickbooks/fetchServices');
                    this.loadingQuickbooksConfig = false;
                }
            },
            
            caregiverAssignmentMode(newVal, oldVal) {
                if (newVal) {
                    this.defaultRates = false;
                }
            },
        },
    }
</script>

<style scoped>
    .table th, .table td {
        padding: 0.35rem 0.5rem;
        min-width: 80px;
    }
    .table td.text-only {
        padding-top: 0.65rem;
    }
    select.payers, select.services {
        min-width: 120px;
    }
    .service-actions {
        min-width: 0px !important;
    }
</style>