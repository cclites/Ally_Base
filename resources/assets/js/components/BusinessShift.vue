<template>
    <div>
        <loading-card v-show="loading" text="Loading Data"></loading-card>
        <div v-show="!loading">
            <div v-if="isOfficeUserOrAdmin">
                <div v-if="billingType === 'services' && serviceHours != duration" class="alert alert-danger">
                    <p><i class="fa fa-exchange mr-1"></i> The caregiver clocked in but the duration does not match what was scheduled.</p>
                    <p>Caregiver clocked in duration: {{ numberFormat(duration) }} hours
                    &nbsp;|&nbsp;
                    Scheduled services duration: {{ numberFormat(serviceHours) }} hours</p>
                    Please adjust accordingly.
                </div>
                <div class="alert alert-warning" v-if="shift.id && !form.checked_out_time">
                    <b>Warning!</b> This shift is currently clocked in.  To clock out this shift, set a Clocked Out Time and click "Save".
                </div>
                <div class="alert alert-warning" v-if="status === 'WAITING_FOR_CONFIRMATION'">
                    <b>Warning!</b> This shift is unconfirmed.  Confirm the details and click "Save &amp; Confirm".
                </div>
            </div>
            <form @submit.prevent="" @keydown="form.clearError($event.target.name)" :class="formClass">
                <b-row v-if="isClient">
                    <b-col lg="6">
                        <b-form-group label="Caregiver" label-for="caregiver">
                            <b-input type="text" readonly :value="shift.caregiver.name" id="caregiver" />
                        </b-form-group>
                    </b-col>
                </b-row>
                <b-row v-else>
                    <b-col lg="6">
                        <b-form-group label="Client" label-for="client_id">
                            <b-form-select
                                    name="client_id"
                                    v-model="form.client_id"
                                    @input="changedClient(form.client_id)"
                            >
                                <option v-for="item in clients" :value="item.id" :key="item.id">{{ item.name }}</option>
                            </b-form-select>
                            <input-help :form="form" field="client_id" text=""></input-help>
                        </b-form-group>
                    </b-col>
                    <b-col lg="6">
                        <b-form-group label="Caregiver" label-for="caregiver_id">
                            <b-form-select
                                    name="caregiver_id"
                                    v-model="form.caregiver_id"
                                    @input="changedCaregiver(form.caregiver_id)"
                            >
                                <option v-for="item in caregivers" :value="item.id" :key="item.id">{{ item.name }}</option>
                            </b-form-select>
                            <input-help :form="form" field="caregiver_id" text=""></input-help>
                        </b-form-group>
                    </b-col>
                </b-row>


                <!-- START NEW SHIFT STRUCTURE -->

                <b-row class="mt-2">
                    <b-col lg="12" class="pb-2">
                        <strong>Actual Shift Timing & Expenses</strong>
                    </b-col>
                    <b-col>
                        <div class="table-responsive">
                            <table class="table table-bordered mb-0">
                                <thead>
                                <tr>
                                    <th style="width: 20%;">Start Date</th>
                                    <th style="width: 20%;">End Date</th>
                                    <th>Start Time</th>
                                    <th>End Time</th>
                                    <th v-if="business.co_mileage">Mileage</th>
                                    <th v-if="business.co_expenses">Other Expenses</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td>
                                        <date-picker v-model="startDate" @input="changedStartDate(startDate)"/>
                                    </td>
                                    <td>
                                        <date-picker v-model="endDate" @input="changedEndDate(endDate)"/>
                                    </td>
                                    <td>
                                        <time-picker name="startTime" v-model="startTime" @input="changedStartTime(startTime)" id="startTime" />
                                    </td>
                                    <td>
                                        <time-picker name="endTime" v-model="endTime" @input="changedEndTime(endTime)" id="endTime" />
                                    </td>
                                    <td v-if="business.co_mileage">
                                        <b-form-input
                                                name="mileage"
                                                type="number"
                                                v-model="form.mileage"
                                                step="any"
                                                :readonly="isClient"
                                        />
                                    </td>
                                    <td v-if="business.co_expenses">
                                        <b-form-input
                                                name="other_expenses"
                                                type="number"
                                                v-model="form.other_expenses"
                                                step="any"
                                                :readonly="isClient"
                                        />
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                        <input-help :form="form" field="checked_in_time" text="" />
                        <input-help :form="form" field="checked_out_time" text="" />
                        <input-help :form="form" field="mileage" text="" />
                        <input-help :form="form" field="other_expenses" text="" />
                    </b-col>
                </b-row>

                <b-row>
                    <b-col v-if="business.co_expenses && form.other_expenses > 0">
                        <b-form-group label="Other Expenses Description" label-for="other_expenses_desc">
                            <b-textarea
                                    id="other_expenses_desc"
                                    name="other_expenses_desc"
                                    :rows="2"
                                    v-model="form.other_expenses_desc"
                                    :readonly="isClient"
                            >
                            </b-textarea>
                            <input-help :form="form" field="other_expenses_desc" text=""></input-help>
                        </b-form-group>
                    </b-col>
                </b-row>

                <div v-if="isOfficeUserOrAdmin">
                    <b-row class="mt-2">
                        <b-col lg="12">
                            <strong>Shift Billing Type</strong>
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
                                <table class="table table-bordered table-fit-more mb-0">
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
                                        <th v-if="allowQuickbooksMapping">Quickbooks Service Mapping</th>
                                        <th class="service-actions"></th>
                                    </tr>
                                    </thead>
                                    <tbody>

                                    <!-- Hourly / Fixed -->
                                    <tr v-if="billingType === 'hourly' || billingType === 'fixed'">
                                        <td>
                                            <b-form-select v-model="form.service_id" class="services" @input="changedService(form, form.service_id)">
                                                <option v-for="( service, i ) in services" :value="service.id" :key=" i ">{{ service.name }} {{ service.code }}</option>
                                            </b-form-select>
                                        </td>
                                        <td>
                                            <b-form-select v-model="form.hours_type" name="hours_type" style="min-width: 80px;" @change="(x) => onChangeHoursType(x, this.form.hours_type)">
                                                <option value="default">REG</option>
                                                <option value="holiday">HOL</option>
                                                <option value="overtime">OT</option>
                                            </b-form-select>
                                        </td>
                                        <td class="text-only">
                                            {{ billingType === 'hourly' ? 'Actual' : 'Fixed' }}
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
                                                    @change="recalculateRates(form, form.client_rate, form.caregiver_rate)"
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
                                                    @change="recalculateRates(form, form.client_rate, form.caregiver_rate)"
                                                    class="money-input"
                                            />
                                        </td>
                                        <td :colspan="allowQuickbooksMapping ? 1 : 2">
                                            <b-form-select v-model="form.payer_id" class="payers" @input="changedPayer(form, form.payer_id)">
                                                <option :value="null">(Auto)</option>
                                                <option v-for="( payer, i ) in clientPayers" :value="payer.id" :key=" i ">{{ payer.name }}</option>
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
                                    <div v-if="billingType === 'services'">
                                        <tr v-for="(service,index) in form.services" :key=" index ">
                                            <td>
                                                <b-form-select v-model="service.service_id" class="services" @input="changedService(service, service.service_id)">
                                                    <option v-for="( s, i ) in services" :value="s.id" :key=" i ">{{ s.name }} {{ s.code }}</option>
                                                </b-form-select>
                                            </td>
                                            <td>
                                                <b-form-select v-model="service.hours_type" name="hours_type" @change="(x) => onChangeServiceHoursType(x, service.hours_type, index)">
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
                                                        @change="recalculateRates(service, service.client_rate, service.caregiver_rate)"
                                                        class="money-input"
                                                />
                                            </td>
                                            <td class="text-only"  v-if="defaultRates">
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
                                                        @change="recalculateRates(service, service.client_rate, service.caregiver_rate)"
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
                                    </div>
                                    </tbody>
                                </table>
                            </div>

                            <div v-if="billingType === 'services' && serviceHours != duration" class="alert alert-warning">
                                Warning: The shift's actual hours ({{ numberFormat(duration) }}) do not match the broken out service hours.
                            </div>
                            <b-alert v-if="isUsingOvertime" variant="warning" show>
                                Note: Because OT/HOL is selected, the rates have been re-calculated to match your settings.
                            </b-alert>

                            <b-alert v-if="isUsingDefaultRates" variant="info" show>
                                This shift is using the default rates.
                            </b-alert>
                            <b-alert v-else variant="warning" show>
                                This shift is not using the default rates.
                            </b-alert>
                            <label class="mt-1">
                                <b-form-checkbox v-model="defaultRates">
                                    Update with Default Rates from Caregivers &amp; Rates Tab of Client Profile on Save
                                </b-form-checkbox>
                            </label>
                        </b-col>
                        <b-col lg="12">
                            <input-help :form="form" field="service_id" text=""></input-help>
                            <input-help :form="form" field="payer_id" text=""></input-help>
                            <input-help :form="form" field="caregiver_rate" text=""></input-help>
                            <input-help :form="form" field="client_rate" text=""></input-help>
                        </b-col>
                    </b-row>
                </div>
                <!-- END NEW SHIFT STRUCTURE -->

                <b-row>
                    <b-col lg="12">
                        <b-form-group v-if="business.co_comments && !isClient" label="Shift Notes &amp; Caregiver Comments" label-for="caregiver_comments">
                            <b-textarea
                                    id="caregiver_comments"
                                    name="caregiver_comments"
                                    :rows="4"
                                    v-model="form.caregiver_comments"
                            >
                            </b-textarea>
                            <input-help :form="form" field="caregiver_comments" text=""></input-help>
                        </b-form-group>
                    </b-col>
                </b-row>
                <b-row>
                    <b-col lg="12">
                        <h5><strong>Activities Performed</strong></h5>
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
                <div v-if="! isClient">
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
                    <b-row class="with-padding-top" v-if="(business.co_issues || business.co_injuries) && !is_modal">
                        <b-col lg="12">
                            <h5>
                                <strong>Shift Issues</strong>
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
                    <shift-evv-data-table v-if="shift.id" :shift="shift"></shift-evv-data-table>
                </div> <!-- // end ! isClient -->

                <edit-code-dropdowns
                    v-if=" shift.id "
                    class="my-3"
                    :visit_edit_action=" form.visit_edit_action "
                    :visit_edit_reason=" form.visit_edit_reason "
                    :updateAction=" updateAction "
                    :updateReason=" updateReason "
                />

                <b-row v-if="isClient">
                    <b-col lg="12" class="text-right mt-3">
                        <b-button variant="info" type="submit" @click="saveShift(false)">
                            Modify Shift
                        </b-button>
                    </b-col>
                </b-row>
                <b-row v-else-if=" !is_modal ">
                    <b-col lg="4">
                        <b-row><span><strong>Added:</strong>&nbsp;{{ shift.created_at ? formatDateTimeFromUTC(shift.created_at) : 'Unknown' }}</span></b-row>
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
                            <b-button variant="info" type="button" @click="saveShift(false)">
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
                            <template slot="button-content" >
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

            <confirmation-modal title="Confirm Potential Duplicate"
                                v-model="confirmModal"
                                @confirm="confirmDuplicate()"
            >
                <div class="text-center">
                    <p>
                        We believe this may be a duplicate shift.  Are you sure you want to continue?
                    </p>
                    <p>
                        The potential duplicate occurred on {{ duplicateDate }}
                    </p>
                </div>
            </confirmation-modal>
        </div>
    </div>
</template>

<script>
    import FormatsNumbers from '../mixins/FormatsNumbers'
    import FormatsDates from "../mixins/FormatsDates";
    import ConfirmationModal from "./modals/ConfirmationModal";
    import ShiftServices from "../mixins/ShiftServices";
    import AuthUser from '../mixins/AuthUser';
    import { mapGetters } from 'vuex';
    import Constants from '../mixins/Constants';

    export default {
        components: {ConfirmationModal},
        mixins: [AuthUser, FormatsNumbers, FormatsDates, ShiftServices, Constants],

        props: {
            'isRoot': {
                type: Boolean,
                default: false,
            },
            'shift': {
                required: true,
                type: Object,
                default() {
                    return {};
                }
            },
            'caregiver': {
                type: Object,
                default() {
                    return {};
                }
            },
            'client': {
                type: Object,
                default() {
                    return {};
                }
            },
            'admin': Number,
            'is_modal': 0,
            'payment_type': {},
            showInactiveClients: {
                type: Boolean,
                default: false,
            },
            showInactiveCaregivers: {
                type: Boolean,
                default: false,
            },
        },
        data() {
            return {
                form: new Form(this.initForm(this.shift)),
                status: (this.shift.id) ? this.shift.status : null,
                startTime: '',
                startDate: '',
                endTime: '',
                endDate: '',
                deleted: false,
                clientAllyPct: 0.05,
                paymentType: 'NONE',  // This is the client payment type, NOT the payment type necessarily used for this shift
                submitting: false,
                duplicateDate: '',
                confirmModal: false,
                loading: false,
                loadingQuickbooksConfig: false,
            }
        },
        async mounted() {
            if (this.shift) {
                this.changedShift(this.shift);
            }
            if (this.isOfficeUserOrAdmin) {
                await this.$store.dispatch('filters/fetchResources', ['clients', 'caregivers', 'services', 'activities']);
            }
            this.loadAllyPctFromClient();
            this.fixDateTimes();
        },
        computed: {
            ...mapGetters({
                activityList: 'filters/activityList',
                clientList: 'filters/clientList',
                caregiverList: 'filters/caregiverList',
                quickbooksServices: 'quickbooks/services',
                quickbooksBusiness: 'quickbooks/businessId',
                quickbooksIsAuthorized: 'quickbooks/isAuthorized',
                quickbooksAllowMapping: 'quickbooks/mapServiceFromShifts',
            }),

            clients() {
                if (this.showInactiveClients) {
                    return this.clientList;
                }

                return this.clientList.filter(x => x.active == 1);
            },
            caregivers() {
                if (this.showInactiveCaregivers) {
                    return this.caregiverList;
                }

                return this.caregiverList.filter(x => x.active == 1);
            },
            activities() {
                if (! this.client || ! this.business.id) {
                    return this.activityList.filter(x => x.business_id == null);
                }
                return this.activityList.filter(x => x.business_id == null || x.business_id == this.business.id);
            },
            selectedClient() {
                return this.form.client_id ? this.clients.find(client => client.id == this.form.client_id) || {} : {};
            },
            business() {
                return this.selectedClient.business_id ? this.$store.getters.getBusiness(this.selectedClient.business_id) : {};
            },
            isClient() {
                return this.authRole === 'client';
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
            totalCost() {
                return (parseFloat(this.totalRate) * parseFloat(this.duration)).toFixed(2);
            },
            duration() {
                var duration = moment.duration(this.getClockedOutMoment().diff(this.getClockedInMoment()));
                return duration.asHours();
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
                if (this.form.fixed_rates === 0) {
                    return 'Hourly';
                }
                if (this.form.fixed_rates === 1) {
                    return 'Daily';
                }
                return '';
            },
            urlPrefix() {
                return this.isClient ? `/unconfirmed-shifts/` : `/business/shifts/`;
            },

            allowQuickbooksMapping() {
                return this.quickbooksAllowMapping && this.quickbooksIsAuthorized;
            },

            disableQuickbooksMapping() {
                return !this.business || this.loadingQuickbooksConfig;
            },

        },
        methods: {

            updateAction( action ){

                this.form.visit_edit_action = action;
            },
            updateReason( reason ){

                this.form.visit_edit_reason = reason;
            },
            changedShift(shift) {
                if (this.isRoot) {
                    // If we are not working from the SHR or other parent
                    // page, we need to inform the filters store what
                    // business we are working with.
                    this.$store.commit('filters/setBusiness', shift.business_id);
                }
                this.resetForm(shift);
                this.changedClient(shift.client_id);
            },

            async changedClient(clientId) {
                if (this.isClient) {
                    return;
                }
                if (clientId) {
                    this.loading = true;
                    try {
                        await this.loadClientPayersAndRatesData(clientId);
                        this.loadAllyPctFromClient(clientId);
                        // await this.loadClientRates(clientId);
                        // await this.loadClientPayers(clientId);
                    }
                    catch (e) {}
                    this.loading = false
                }
            },

            changedCaregiver(caregiverId) {
                this.fetchAllRates();
            },

            changedStartDate(startDate) {
                this.validateTimeDifference('checked_in_time');
                this.fetchAllRates();
            },

            changedEndDate(endDate) {
                this.validateTimeDifference('checked_out_time');
            },

            changedStartTime(startTime) {
                this.validateTimeDifference('checked_in_time');
            },

            changedEndTime(endTime) {
                this.validateTimeDifference('checked_out_time');
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

            resetForm(shift) {
                if (!shift) {
                    shift = {};
                }

                // Reset values
                this.deleted = false;
                this.billingType = shift.fixed_rates ? 'fixed' : 'hourly';
                this.defaultRates = false; // always set to false for shifts.

                // Initialize form
                this.$nextTick(() => {
                    this.form = new Form(this.initForm(shift));
                    this.recalculateRates(this.form, this.form.client_rate, this.form.caregiver_rate);

                    if (shift) {
                        // Initialize form values from services
                        if (this.isOfficeUserOrAdmin) {
                            this.initServicesFromObject(shift);
                        }

                        // Initialize additional data from shift
                        this.status = (this.shift.id) ? this.shift.status : null;
                        this.fixDateTimes();
                    }
                });
            },

            setDefaultDateTimes() {
                this.startDate = moment().format('MM/DD/YYYY');
                this.endDate = moment().format('MM/DD/YYYY');
                this.startTime = '09:00';
                this.endTime = '10:00';
            },

            fixDateTimes() {
                // Do not check against id below to allow for shift duplication
                if (this.shift.checked_in_time) {
                    let checkin = moment.utc(this.shift.checked_in_time).local();
                    let checkout = (this.shift.checked_out_time) ? moment.utc(this.shift.checked_out_time).local() : null;
                    this.startDate = checkin.format('MM/DD/YYYY');
                    this.startTime = checkin.format('HH:mm');
                    this.endDate = (checkout) ? checkout.format('MM/DD/YYYY') : null;
                    this.endTime = (checkout) ? checkout.format('HH:mm') : null;
                }
                else {
                    this.setDefaultDateTimes();
                }
            },

            initForm(shift = {}) {
                return {
                    client_id: shift.id ? shift.client_id : this.client.id || null,
                    caregiver_id: shift.id ? shift.caregiver_id : this.caregiver.id || null,
                    caregiver_comments: shift.caregiver_comments || null,
                    checked_in_time: shift.checked_in_time || null,
                    checked_out_time: shift.checked_out_time || null,
                    mileage: shift.mileage || 0,
                    other_expenses: shift.other_expenses || 0,
                    other_expenses_desc: shift.other_expenses_desc || null,
                    hours_type: shift.hours_type || 'default',
                    verified: shift.verified || true,
                    fixed_rates: shift.fixed_rates || 0,
                    caregiver_rate: shift.caregiver_rate || '',
                    client_rate: shift.client_rate || '',
                    provider_fee: null, // for show
                    ally_fee: null, // for show
                    service_id: shift.service_id || (this.defaultService ? this.defaultService.id : null),
                    payer_id: shift.payer_id || shift.payer_id == 0 ? shift.payer_id : null,
                    activities: this.getShiftActivityList(),
                    issues: shift.issues || [],
                    override: false,
                    duplicate_confirm: 0,
                    modal: this.is_modal,
                    goals: this.isClient ? {} : this.setupGoalsForm(),
                    questions: this.isClient ? {} : this.setupQuestionsForm(),
                    services: [], // added by initServicesFromObject
                    default_rates: {
                        'client_rate': null,
                        'caregiver_rate': null,
                        'provider_fee': null,
                        'ally_fee': null,
                    },
                    quickbooks_service_id: shift.quickbooks_service_id || '',
                    visit_edit_reason : shift.visit_edit_reason,
                    visit_edit_action : shift.visit_edit_action
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
                return moment(this.startDate + ' ' + this.startTime, 'MM/DD/YYYY HH:mm');
            },
            getClockedOutMoment() {
                return moment(this.endDate + ' ' + this.endTime, 'MM/DD/YYYY HH:mm');
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

                // Finalize the timings
                this.form.checked_in_time = this.getClockedInMoment().format();
                this.form.checked_out_time = this.getClockedOutMoment().format();

                // Finalize form billing type
                if (this.billingType === 'services') {
                    this.form.service_id = null;
                    this.form.payer_id = null;
                    this.form.fixed_rates = false;
                } else {
                    this.form.services = [];
                    this.form.fixed_rates = (this.billingType === 'fixed');
                }

                if (this.shift.id) {
                    try {
                        let response = await this.form.patch(`${this.urlPrefix}${this.shift.id}`);
                        if (confirm) {
                            try {
                                let form = new Form();
                                let confirmResponse = await form.post(`${this.urlPrefix}${this.shift.id}/confirm`);
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
                    this.form.hideErrorsFor(449).post('/business/shifts').then(response => {
                        this.$emit('shift-created', response.data.data.shift.id);
                        this.status = response.data.data.status;
                        this.submitting = false;
                    }).catch(error => {
                        if (error.response.status === 449) {
                            let duplicate = error.response.data.data;
                            this.duplicateDate = this.formatDateTimeFromUTC(duplicate.checked_in_time) + ' - '
                                + this.formatTimeFromUTC(duplicate.checked_out_time);
                            this.confirmModal = true;
                        }
                        this.submitting = false;
                    });
                }
            },
            adminOverride() {
                this.form.override = 1;
                return this.saveShift();
            },
            confirmDuplicate() {
                this.form.duplicate_confirm = 1;
                return this.saveShift();
            },
            unconfirm() {
                if (this.shift.id) {
                    let form = new Form();
                    form.post('/business/shifts/' + this.shift.id + '/unconfirm')
                        .then(response => {
                            this.status = response.data.data.status;
                            this.$emit('shift-updated', this.shift.id);
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

                        if (clockout.diff(moment(), 'hours') > this.SHIFT_MAX_FUTURE_END_DATE) {
                            this.form.addError(field, 'The clock out time cannot be more than '+this.SHIFT_MAX_FUTURE_END_DATE+' hours from now.');
                        }
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
            loadCaregiverRates() {
                if (!this.form.caregiver_id || !this.form.client_id) return;
                axios.get('/business/clients/' + this.form.client_id + '/caregivers/' + this.form.caregiver_id).then(response => {
                    if (response.data.rates) {
                        let rates = response.data.rates[this.rateType.toLowerCase()];
                        this.form.caregiver_rate = rates.caregiver_rate.toFixed(2);
                        this.form.provider_fee = rates.provider_fee.toFixed(2);
                    }
                });
            },
            loadAllyPctFromClient() {
                if (this.payment_type) {
                    this.clientAllyPct = this.payment_type.percentage_fee;
                    this.paymentType = this.payment_type.payment_type;
                    return;
                }

                if (!this.form.client_id) return;
                // axios.get(`/business/clients/${this.form.client_id}/payment_type`).then(response => {
                //     this.clientAllyPct = response.data.percentage_fee;
                //     this.paymentType = response.data.payment_type;
                // });
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

            shift(newVal, oldVal) {
                if (newVal.id !== oldVal.id) this.changedShift(newVal);
            },

            allyPct() {
                this.recalculateAllRates(this.form)
            },

            // Watch if the business changes and refresh the current quickbooks settings.
            async business(newVal, oldVal) {
                if (newVal && newVal.id) {
                    this.loadingQuickbooksConfig = true;
                    await this.$store.dispatch('quickbooks/fetchConfig', newVal.id);
                    await this.$store.dispatch('quickbooks/fetchServices');
                    this.loadingQuickbooksConfig = false;
                }
            },
        },
    }
</script>

<style scoped>
    .deletedForm {
        opacity: 0.3;
    }
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

