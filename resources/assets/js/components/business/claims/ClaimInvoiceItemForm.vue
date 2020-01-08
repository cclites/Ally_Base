<template>
    <div>
        <b-row>
            <b-col lg="6">
                <b-form-group label="Claimable Type" label-for="type" class="bold">
                    <label v-if="item.id">{{ item.type }}</label>
                    <div v-else>
                    <b-select
                        v-model="form.claimable_type"
                        id="claimable_type"
                        name="claimable_type"
                        :disabled="form.busy"
                    >
                        <option :value="CLAIMABLE_TYPES.SERVICE">Service</option>
                        <option :value="CLAIMABLE_TYPES.EXPENSE">Expense</option>
                    </b-select>
                        <input-help :form="form" field="claimable_type" text="" />
                    </div>
                </b-form-group>
            </b-col>
            <b-col lg="6">
                <b-form-group label="Related Shift" label-for="shift_id" class="bold">
                    <label v-if="item.related_shift_id">
                        <a :href="`/business/shifts/${item.related_shift_id}`" target="_blank">{{ item.related_shift_id }}</a>
                    </label>
                    <label v-else>N/A</label>
                </b-form-group>
            </b-col>
        </b-row>

        <!-- =========== CLAIMABLE EXPENSES =========== -->
        <div v-if="form.claimable_type == CLAIMABLE_TYPES.EXPENSE">
            <b-row>
                <b-col lg="6">
                    <b-form-group label="Caregiver" label-for="caregiver_id">
                        <b-select name="caregiver_id" id="caregiver_id" v-model="form.caregiver_id" @change="onChangeCaregiver()">
                            <option value="">-- Select a Caregiver --</option>
                            <option v-for="item in caregivers" :key="item.id" :value="item.id">{{ item.nameLastFirst }}</option>
                        </b-select>
                        <input-help :form="form" field="caregiver_id" text=""></input-help>
                    </b-form-group>
                </b-col>
                <b-col lg="6">
                </b-col>
            </b-row>
            <b-row>
                <b-col lg="6">
                    <b-form-group label="Caregiver First Name" label-for="caregiver_first_name" label-class="required">
                        <b-form-input
                            v-model="form.caregiver_first_name"
                            id="caregiver_first_name"
                            name="caregiver_first_name"
                            type="text"
                            :disabled="form.busy || form.caregiver_reload"
                        />
                        <input-help :form="form" field="caregiver_first_name" text="" />
                    </b-form-group>
                </b-col>
                <b-col lg="6">
                    <b-form-group label="Caregiver Last Name" label-for="caregiver_last_name" label-class="required">
                        <b-form-input
                            v-model="form.caregiver_last_name"
                            id="caregiver_last_name"
                            name="caregiver_last_name"
                            type="text"
                            :disabled="form.busy || form.caregiver_reload"
                        />
                        <input-help :form="form" field="caregiver_last_name" text="" />
                    </b-form-group>
                </b-col>
            </b-row>

            <b-row>
                <b-col lg="6">
                    <b-form-group label="Client" label-for="client_id">
                        <b-select name="client_id" id="client_id" v-model="form.client_id" @change="onChangeClient()">
                            <option value="">-- Select a Client --</option>
                            <option v-for="item in clients" :key="item.id" :value="item.id">{{ item.name }}</option>
                        </b-select>
                        <input-help :form="form" field="client_id" text=""></input-help>
                    </b-form-group>
                </b-col>
                <b-col lg="6">
                </b-col>
            </b-row>
            <b-row>
                <b-col lg="6">
                    <b-form-group label="First Name" label-for="client_first_name" label-class="required">
                        <b-form-input
                            v-model="form.client_first_name"
                            id="client_first_name"
                            name="client_first_name"
                            type="text"
                            :disabled="form.busy || form.client_reload"
                        />
                        <input-help :form="form" field="client_first_name" text="" />
                    </b-form-group>
                </b-col>
                <b-col lg="6">
                    <b-form-group label="Last Name" label-for="client_last_name" label-class="required">
                        <b-form-input
                            v-model="form.client_last_name"
                            id="client_last_name"
                            name="client_last_name"
                            type="text"
                            :disabled="form.busy || form.client_reload"
                        />
                        <input-help :form="form" field="client_last_name" text="" />
                    </b-form-group>
                </b-col>
            </b-row>

            <b-row>
                <b-col sm="4">
                    <b-form-group label="Name" label-for="name" label-class="required">
                        <b-form-input
                            v-model="form.name"
                            id="name"
                            name="name"
                            type="text"
                            :disabled="form.busy"
                        />
                        <input-help :form="form" field="name" text="" />
                    </b-form-group>
                </b-col>
                <b-col sm="2">
                    <b-form-group label="Date" label-for="date" label-class="required">
                        <mask-input v-model="form.date" id="date" type="date" :disabled="form.busy" />
                        <input-help :form="form" field="date" text="" />
                    </b-form-group>
                </b-col>
                <b-col sm="2">
                    <b-form-group label="Rate" label-for="rate" label-class="required">
                        <b-form-input
                            name="rate"
                            type="number"
                            step="0.01"
                            min="0"
                            max="999.99"
                            v-model="form.rate"
                            class="money-input"
                            @change="recalculateAmount()"
                            :disabled="form.busy"
                        />
                        <input-help :form="form" field="rate" text="" />
                    </b-form-group>
                </b-col>
                <b-col sm="2">
                    <b-form-group label="Units" label-for="units" label-class="required">
                        <b-form-input
                            name="units"
                            type="number"
                            step="0.01"
                            min="0"
                            max="999.99"
                            v-model="form.units"
                            class="money-input"
                            @change="recalculateAmount()"
                            :disabled="form.busy"
                        />
                        <input-help :form="form" field="units" text="" />
                    </b-form-group>
                </b-col>
                <b-col sm="2">
                    <b-form-group label="Amount" label-for="amount" label-class="required">
                        <b-form-input
                            name="amount"
                            type="number"
                            step="0.01"
                            min="0"
                            max="999.99"
                            v-model="form.amount"
                            @change="recalculateRate()"
                            class="money-input"
                            :disabled="form.busy"
                        />
                        <input-help :form="form" field="amount" text="" />
                    </b-form-group>
                </b-col>
            </b-row>
            <b-row>
                <b-col lg="12">
                    <b-form-group label="Notes" label-for="notes">
                        <b-textarea
                            id="notes"
                            name="notes"
                            :rows="2"
                            v-model="form.notes"
                            :disabled="form.busy"
                        />
                        <input-help :form="form" field="notes" text="" />
                    </b-form-group>
                </b-col>
            </b-row>
        </div>
        <!-- =========== /END CLAIMABLE EXPENSES =========== -->

        <!-- =========== CLAIMABLE SERVICES =========== -->
        <div v-else>
            <h5><strong>Shift Dates</strong></h5>
            <b-row>
                <b-col lg="3">
                    <b-form-group label="Start Date" label-for="shift_start_date" label-class="required">
                        <date-picker v-model="form.shift_start_date" id="shift_start_date" :disabled="form.busy" />
                        <input-help :form="form" field="shift_start_date" text="" />
                    </b-form-group>
                </b-col>
                <b-col lg="3">
                    <b-form-group label="End Date" label-for="shift_end_date" label-class="required">
                        <date-picker v-model="form.shift_end_date" id="shift_end_date" :disabled="form.busy" />
                        <input-help :form="form" field="shift_end_date" text="" />
                    </b-form-group>
                </b-col>
                <b-col lg="3">
                    <b-form-group label="Start Time" label-for="shift_start_time" label-class="required">
                        <time-picker v-model="form.shift_start_time" id="shift_start_time" :disabled="form.busy" />
                        <input-help :form="form" field="shift_start_time" text="" />
                    </b-form-group>
                </b-col>
                <b-col lg="3">
                    <b-form-group label="End Time" label-for="shift_end_time" label-class="required">
                        <time-picker v-model="form.shift_end_time" id="shift_end_time" :disabled="form.busy" />
                        <input-help :form="form" field="shift_end_time" text="" />
                    </b-form-group>
                </b-col>
            </b-row>

            <h5><strong>Service</strong></h5>
            <b-row>
                <b-col lg="4">
                    <b-form-group label="Service" label-for="service_id">
                        <b-select name="service_id" id="service_id" v-model="form.service_id" @change="onChangeService">
                            <option value="">-- Choose Service --</option>
                            <option v-for="item in services" :key="item.id" :value="item.id">{{ item.display }}</option>
                        </b-select>
                        <input-help :form="form" field="service_id" text=""></input-help>
                    </b-form-group>
                </b-col>
                <b-col lg="4">
                    <b-form-group label="Service Name" label-for="service_name" label-class="required">
                        <b-form-input
                            v-model="form.service_name"
                            id="service_name"
                            name="service_name"
                            type="text"
                            :disabled="form.busy"
                        />
                        <input-help :form="form" field="service_name" text=""></input-help>
                    </b-form-group>
                </b-col>
                <b-col lg="4">
                    <b-form-group label="Service Code" label-for="service_code">
                        <b-form-input
                            v-model="form.service_code"
                            id="service_code"
                            name="service_code"
                            type="text"
                            :disabled="form.busy"
                        />
                        <input-help :form="form" field="service_code" text=""></input-help>
                    </b-form-group>
                </b-col>
            </b-row>
            <b-row>
                <b-col lg="3">
                    <b-form-group label="Start Date" label-for="service_start_date" label-class="required">
                        <date-picker v-model="form.service_start_date" id="service_start_date" :disabled="form.busy" />
                        <input-help :form="form" field="service_start_date" text="" />
                    </b-form-group>
                </b-col>
                <b-col lg="3">
                    <b-form-group label="Start Time" label-for="service_start_time" label-class="required">
                        <time-picker v-model="form.service_start_time" id="service_start_time" :disabled="form.busy" />
                        <input-help :form="form" field="service_start_time" text="" />
                    </b-form-group>
                </b-col>
                <b-col sm="2">
                    <b-form-group label="Hours" label-for="units" label-class="required">
                        <b-form-input
                            id="units"
                            name="units"
                            type="number"
                            step="0.01"
                            min="0"
                            max="999.99"
                            v-model="form.units"
                            class="money-input"
                            @change="recalculateAmount()"
                            :disabled="form.busy"
                        />
                        <input-help :form="form" field="units" text="" />
                    </b-form-group>
                </b-col>
                <b-col sm="2">
                    <b-form-group label="Client Rate" label-for="rate" label-class="required">
                        <b-form-input
                            id="rate"
                            name="rate"
                            type="number"
                            step="0.01"
                            min="0"
                            max="999.99"
                            v-model="form.rate"
                            class="money-input"
                            @change="recalculateAmount()"
                            :disabled="form.busy"
                        />
                        <input-help :form="form" field="rate" text="" />
                    </b-form-group>
                </b-col>
                <b-col sm="2">
                    <b-form-group label="Total Amount" label-for="amount" label-class="required">
                        <b-form-input
                            id="amount"
                            name="amount"
                            type="number"
                            step="0.01"
                            min="0"
                            max="999.99"
                            v-model="form.amount"
                            @change="recalculateRate()"
                            class="money-input"
                            :disabled="form.busy"
                        />
                        <input-help :form="form" field="amount" text="" />
                    </b-form-group>
                </b-col>
            </b-row>
            <b-row>
                <b-col lg="12">
                    <b-form-group label="Activity Codes" label-for="activities">
                        <b-textarea
                            v-model="form.activities"
                            id="activities"
                            name="activities"
                            type="text"
                            :disabled="form.busy"
                            rows="2"
                        />
                        <small class="form-text text-muted">
                            List the performed activity codes (separated by comma).  See the <a href="/business/activities" target="_blank">Activity List</a> for help.
                        </small>
                    </b-form-group>
                    <b-form-group label="Caregiver Comments" label-for="caregiver_comments">
                        <b-textarea
                            v-model="form.caregiver_comments"
                            id="caregiver_comments"
                            name="caregiver_comments"
                            type="text"
                            :disabled="form.busy"
                            rows="2"
                        />
                        <input-help :form="form" field="caregiver_comments" text="The comments by the Caregiver during clock out." />
                    </b-form-group>
                </b-col>
            </b-row>
            <b-row class="mb-3">
                <b-col md="4">
                    <label>Is Overtime/HOL</label>
                    <div>{{ form.is_overtime ? 'Yes' : 'No' }}</div>
                </b-col>
                <b-col md="4">
                    <label>Has Client Signature</label>
                    <div>{{ form.client_signature_id ? 'Yes' : 'No' }}</div>
                </b-col>
                <b-col md="4">
                    <label>Has Caregiver Signature</label>
                    <div>{{ form.caregiver_signature_id ? 'Yes' : 'No' }}</div>
                </b-col>
            </b-row>

            <h5><strong>Client Information</strong></h5>
            <b-row>
                <b-col lg="6">
                    <b-form-group label="Client" label-for="client_id">
                        <b-select name="client_id" id="client_id" v-model="form.client_id" @change="onChangeClient()">
                            <option value="">-- Select a Client --</option>
                            <option v-for="item in clients" :key="item.id" :value="item.id">{{ item.name }}</option>
                        </b-select>
                        <input-help :form="form" field="client_id" text=""></input-help>
                    </b-form-group>
                </b-col>
                <b-col lg="6">
                </b-col>
            </b-row>
            <b-row v-if="form.client_reload">
                <b-col>
                    <b-alert show variant="info">This data will be updated with the databased Client values on save.</b-alert>
                </b-col>
            </b-row>
            <b-row>
                <b-col lg="6">
                    <b-form-group label="First Name" label-for="client_first_name" label-class="required">
                        <b-form-input
                            v-model="form.client_first_name"
                            id="client_first_name"
                            name="client_first_name"
                            type="text"
                            :disabled="form.busy || form.client_reload"
                        />
                        <input-help :form="form" field="client_first_name" text="" />
                    </b-form-group>
                </b-col>
                <b-col lg="6">
                    <b-form-group label="Last Name" label-for="client_last_name" label-class="required">
                        <b-form-input
                            v-model="form.client_last_name"
                            id="client_last_name"
                            name="client_last_name"
                            type="text"
                            :disabled="form.busy || form.client_reload"
                        />
                        <input-help :form="form" field="client_last_name" text="" />
                    </b-form-group>
                </b-col>
            </b-row>
            <b-row>
                <b-col lg="6">
                    <b-form-group label="Date of Birth" label-for="client_dob">
                        <mask-input v-model="form.client_dob" id="client_dob" type="date" :disabled="form.busy || form.client_reload"></mask-input>
                        <input-help :form="form" field="date_of_birth" text="Enter their date of birth. Ex: MM/DD/YYYY"></input-help>
                    </b-form-group>
                </b-col>
                <b-col lg="6">
                    <b-form-group label="Case Manager" label-for="client_case_manager">
                        <b-form-input
                            v-model="form.client_case_manager"
                            id="client_case_manager"
                            name="client_case_manager"
                            type="text"
                            :disabled="form.busy || form.client_reload"
                        />
                        <input-help :form="form" field="client_case_manager" text=""></input-help>
                    </b-form-group>
                </b-col>
            </b-row>
            <b-row>
                <b-col lg="6">
                    <b-form-group label="Medicaid ID" label-for="client_medicaid_id">
                        <b-form-input
                            v-model="form.client_medicaid_id"
                            id="client_medicaid_id"
                            name="client_medicaid_id"
                            type="text"
                            :disabled="form.busy || form.client_reload"
                        ></b-form-input>
                        <input-help :form="form" field="client_medicaid_id" text=""></input-help>
                    </b-form-group>
                </b-col>
                <b-col lg="6">
                    <b-form-group label="Medicaid Diagnosis Codes" label-for="client_medicaid_diagnosis_codes">
                        <b-form-input
                            v-model="form.client_medicaid_diagnosis_codes"
                            id="client_medicaid_diagnosis_codes"
                            name="client_medicaid_diagnosis_codes"
                            type="text"
                            :disabled="form.busy || form.client_reload"
                        ></b-form-input>
                        <input-help :form="form" field="client_medicaid_diagnosis_codes" text=""></input-help>
                    </b-form-group>
                </b-col>
            </b-row>
            <b-row>
                <b-col lg="6">
                    <b-form-group label="Policy #" label-for="client_ltci_policy_number">
                        <b-form-input
                            v-model="form.client_ltci_policy_number"
                            id="client_ltci_policy_number"
                            name="client_ltci_policy_number"
                            type="text"
                            :disabled="form.busy || form.client_reload"
                        />
                        <input-help :form="form" field="client_ltci_policy_number" text=""></input-help>
                    </b-form-group>
                </b-col>
                <b-col lg="6">
                    <b-form-group label="Claim #" label-for="client_ltci_claim_number">
                        <b-form-input
                            v-model="form.client_ltci_claim_number"
                            id="client_ltci_claim_number"
                            name="client_ltci_claim_number"
                            type="text"
                            :disabled="form.busy || form.client_reload"
                        />
                        <input-help :form="form" field="client_ltci_claim_number" text=""></input-help>
                    </b-form-group>
                </b-col>
            </b-row>
            <b-row>
                <b-col lg="6">
                    <b-form-group label="HIC" label-for="client_hic">
                        <b-form-input
                            v-model="form.client_hic"
                            id="client_hic"
                            name="client_hic"
                            type="text"
                            :disabled="form.busy || form.client_reload"
                        />
                        <input-help :form="form" field="client_hic" text=""></input-help>
                    </b-form-group>
                </b-col>
                <b-col lg="6">
                    <b-form-group label="Print On Claim Invoice" label-for="client_invoice_notes">
                        <b-form-input
                            v-model="form.client_invoice_notes"
                            id="client_invoice_notes"
                            name="client_invoice_notes"
                            type="text"
                            :disabled="form.busy || form.client_reload"
                        />
                        <input-help :form="form" field="client_invoice_notes" text=""></input-help>
                    </b-form-group>
                </b-col>
            </b-row>
            <b-row>
                <b-col lg="6">
                    <b-form-group label="Payer Program ID" label-for="client_program_number">
                        <b-form-input
                            v-model="form.client_program_number"
                            id="client_program_number"
                            name="client_program_number"
                            type="text"
                            :disabled="form.busy || form.client_reload"
                        />
                        <input-help :form="form" field="client_program_number" text=""></input-help>
                    </b-form-group>
                </b-col>
                <b-col lg="6">
                    <b-form-group label="Payer CIRTS ID" label-for="client_cirts_number">
                        <b-form-input
                            v-model="form.client_cirts_number"
                            id="client_cirts_number"
                            name="client_cirts_number"
                            type="text"
                            :disabled="form.busy || form.client_reload"
                        />
                        <input-help :form="form" field="client_cirts_number" text=""></input-help>
                    </b-form-group>
                </b-col>
            </b-row>

            <h5><strong>Caregiver Information</strong></h5>
            <b-row>
                <b-col lg="6">
                    <b-form-group label="Caregiver" label-for="caregiver_id">
                        <b-select name="caregiver_id" id="caregiver_id" v-model="form.caregiver_id" @change="onChangeCaregiver()">
                            <option value="">-- Select a Caregiver --</option>
                            <option v-for="item in caregivers" :key="item.id" :value="item.id">{{ item.nameLastFirst }}</option>
                        </b-select>
                        <input-help :form="form" field="caregiver_id" text=""></input-help>
                    </b-form-group>
                </b-col>
                <b-col lg="6">
                </b-col>
            </b-row>
            <b-row v-if="form.caregiver_reload">
                <b-col>
                    <b-alert show variant="info">This data will be updated with the databased Caregiver values on save.</b-alert>
                </b-col>
            </b-row>
            <b-row>
                <b-col lg="6">
                    <b-form-group label="First Name" label-for="caregiver_first_name" label-class="required">
                        <b-form-input
                            v-model="form.caregiver_first_name"
                            id="caregiver_first_name"
                            name="caregiver_first_name"
                            type="text"
                            :disabled="form.busy || form.caregiver_reload"
                        />
                        <input-help :form="form" field="caregiver_first_name" text="" />
                    </b-form-group>
                </b-col>
                <b-col lg="6">
                    <b-form-group label="Last Name" label-for="caregiver_last_name" label-class="required">
                        <b-form-input
                            v-model="form.caregiver_last_name"
                            id="caregiver_last_name"
                            name="caregiver_last_name"
                            type="text"
                            :disabled="form.busy || form.caregiver_reload"
                        />
                        <input-help :form="form" field="caregiver_last_name" text="" />
                    </b-form-group>
                </b-col>
            </b-row>
            <b-row>
                <b-col lg="6">
                    <b-form-group label="Gender" label-for="caregiver_gender">
                        <b-select name="caregiver_gender"
                            id="caregiver_gender"
                            v-model="form.caregiver_gender"
                            :disabled="form.busy || form.caregiver_reload"
                        >
                            <option value="">-- Select Gender --</option>
                            <option value="M">Male</option>
                            <option value="F">Female</option>
                        </b-select>
                        <input-help :form="form || form.caregiver_reload" field="caregiver_gender" text="" />
                    </b-form-group>
                </b-col>
                <b-col lg="6">
                    <b-form-group label="Date of Birth" label-for="caregiver_dob">
                        <mask-input v-model="form.caregiver_dob"
                                id="caregiver_dob"
                                type="date"
                                :disabled="form.busy || form.caregiver_reload" />
                        <input-help :form="form || form.caregiver_reload" field="caregiver_dob" text="" />
                    </b-form-group>
                </b-col>
            </b-row>
            <b-row>
                <b-col lg="6">
                    <b-form-group label="SSN" label-for="caregiver_ssn">
                        <mask-input
                            v-model="form.caregiver_ssn"
                            id="caregiver_ssn"
                            name="caregiver_ssn"
                            type="ssn"
                            :disabled="form.busy || form.caregiver_reload"
                        />
                        <input-help :form="form" field="caregiver_ssn" text="SSN numbers are always masked for security purposes.  If this field is empty there is no value." />
                    </b-form-group>
                </b-col>
                <b-col lg="6">
                    <b-form-group label="Medicaid ID" label-for="caregiver_medicaid_id">
                        <b-form-input
                            v-model="form.caregiver_medicaid_id"
                            id="caregiver_medicaid_id"
                            name="caregiver_medicaid_id"
                            type="text"
                            :disabled="form.busy || form.caregiver_reload"
                        />
                        <input-help :form="form" field="caregiver_medicaid_id" text="" />
                    </b-form-group>
                </b-col>
            </b-row>

            <h5><strong>Service Address</strong></h5>
            <b-row>
                <b-col lg="6">
                    <b-form-group label="Address Line 1" label-for="address1" label-class="required">
                        <b-form-input
                            name="address1"
                            type="text"
                            v-model="form.address1"
                            id="address1"
                            :disabled="form.busy"
                        />
                        <input-help :form="form" field="address1" text="" />
                    </b-form-group>
                    <b-form-group label="Address Line 2" label-for="address2">
                        <b-form-input
                            name="address2"
                            type="text"
                            v-model="form.address2"
                            id="address2"
                            :disabled="form.busy"
                        />
                        <input-help :form="form" field="address2" text="" />
                    </b-form-group>
                    <b-form-group label="City" label-for="city" label-class="required">
                        <b-form-input
                            name="city"
                            type="text"
                            v-model="form.city"
                            id="city"
                            :disabled="form.busy"
                        />
                        <input-help :form="form" field="city" text="" />
                    </b-form-group>
                    <b-form-group label="State" label-for="state" label-class="required">
                        <b-form-input
                            name="state"
                            type="text"
                            v-model="form.state"
                            id="state"
                            :disabled="form.busy"
                        />
                        <input-help :form="form" field="state" text="" />
                    </b-form-group>
                </b-col>
                <b-col lg="6">
                    <b-form-group label="Zip Code" label-for="zip" label-class="required">
                        <b-form-input
                            name="zip"
                            type="text"
                            v-model="form.zip"
                            id="zip"
                            :disabled="form.busy"
                        />
                        <input-help :form="form" field="zip" text="" />
                    </b-form-group>
                    <b-form-group label="Latitude" label-for="latitude">
                        <b-form-input
                            v-model="form.latitude"
                            id="latitude"
                            name="latitude"
                            type="text"
                            :disabled="true"
                        />
                        <input-help :form="form" field="latitude" text="This will automatically update on save." />
                    </b-form-group>
                    <b-form-group label="Longitude" label-for="longitude">
                        <b-form-input
                            v-model="form.longitude"
                            id="longitude"
                            name="longitude"
                            type="text"
                            :disabled="true"
                        />
                        <input-help :form="form" field="longitude" text="This will automatically update on save." />
                    </b-form-group>
                </b-col>
            </b-row>

            <h5><strong>EVV Data</strong></h5>
            <b-row>
                <b-col lg="6">
                    <b-form-group label="Clock-in EVV Method" label-for="evv_method_in">
                        <b-select name="evv_method_in" id="evv_method_in" v-model="form.evv_method_in"
                                  :disabled="true">
                            <option :value="null">None</option>
                            <option value="telephony">Telephony</option>
                            <option value="geolocation">Geolocation</option>
                        </b-select>
                        <input-help :form="form" field="evv_method_in" text=""></input-help>
                    </b-form-group>
                    <div v-if="form.evv_method_in != null">
                        <b-form-group label="Clock-in EVV Time" label-for="evv_start_time">
                            <b-form-input
                                :value="formatDateTimeFromUTC(form.evv_start_time)"
                                id="evv_start_time"
                                name="evv_start_time"
                                type="text"
                                :disabled="true"
                            />
                            <input-help :form="form" field="evv_start_time" text="" />
                        </b-form-group>
                    </div>
                    <div v-if="form.evv_method_in == 'telephony'">
                        <b-form-group label="Clock-in Phone Number" label-for="checked_in_number">
                            <mask-input type="phone"
                                        id="checked_in_number"
                                        name="checked_in_number"
                                        v-model="form.checked_in_number"
                                        :disabled="true"
                            />
                            <input-help :form="form" field="checked_in_number" text="" />
                        </b-form-group>
                    </div>
                    <div v-if="form.evv_method_in == 'geolocation'">
                        <b-form-group label="Clock-in Latitude" label-for="checked_in_latitude">
                            <b-form-input
                                v-model="form.checked_in_latitude"
                                id="checked_in_latitude"
                                name="checked_in_latitude"
                                type="number"
                                step="any"
                                min="-999.99999999999"
                                max="999.99999999999"
                                :disabled="true"
                            />
                            <input-help :form="form" field="checked_in_latitude" text="" />
                        </b-form-group>
                        <b-form-group label="Clock-in Longitude" label-for="checked_in_longitude">
                            <b-form-input
                                v-model="form.checked_in_longitude"
                                id="checked_in_longitude"
                                name="checked_in_longitude"
                                type="number"
                                step="any"
                                min="-999.99999999999"
                                max="999.99999999999"
                                :disabled="true"
                            />
                            <input-help :form="form" field="checked_in_longitude" text="" />
                        </b-form-group>
                    </div>
                </b-col>
                <b-col lg="6">
                    <b-form-group label="Clock-out EVV Method" label-for="evv_method_out">
                        <b-select name="evv_method_out" id="evv_method_out" v-model="form.evv_method_out"
                                  :disabled="true">
                            <option :value="null">None</option>
                            <option value="telephony">Telephony</option>
                            <option value="geolocation">Geolocation</option>
                        </b-select>
                        <input-help :form="form" field="evv_method_out" text=""></input-help>
                    </b-form-group>
                    <div v-if="form.evv_method_in != null">
                        <b-form-group label="Clock-in EVV Time" label-for="evv_end_time">
                            <b-form-input
                                :value="formatDateTimeFromUTC(form.evv_end_time)"
                                id="evv_end_time"
                                name="evv_end_time"
                                type="text"
                                :disabled="true"
                            />
                            <input-help :form="form" field="evv_end_time" text="" />
                        </b-form-group>
                    </div>
                    <div v-if="form.evv_method_out == 'telephony'">
                        <b-form-group label="Clock-out Phone Number" label-for="checked_out_number">
                            <mask-input type="phone"
                                        id="checked_out_number"
                                        name="checked_out_number"
                                        v-model="form.checked_out_number"
                                        :disabled="true"
                            />
                            <input-help :form="form" field="checked_out_number" text="" />
                        </b-form-group>
                    </div>
                    <div v-if="form.evv_method_out == 'geolocation'">
                        <b-form-group label="Clock-out Latitude" label-for="checked_out_latitude">
                            <b-form-input
                                v-model="form.checked_out_latitude"
                                id="checked_out_latitude"
                                name="checked_out_latitude"
                                type="number"
                                step="any"
                                min="-999.99999999999"
                                max="999.99999999999"
                                :disabled="true"
                            />
                            <input-help :form="form" field="checked_out_latitude" text="" />
                        </b-form-group>
                        <b-form-group label="Clock-out Longitude" label-for="checked_out_longitude">
                            <b-form-input
                                v-model="form.checked_out_longitude"
                                id="checked_out_longitude"
                                name="checked_out_longitude"
                                type="number"
                                step="any"
                                min="-999.99999999999"
                                max="999.99999999999"
                                :disabled="true"
                            />
                            <input-help :form="form" field="checked_out_longitude" text="" />
                        </b-form-group>
                    </div>
                </b-col>
            </b-row>
        </div>
        <!-- =========== /END CLAIMABLE SERVICES =========== -->

        <hr />
        <div class="d-flex">
            <div class="ml-auto">
                <b-btn variant="success" @click="save()" :disabled="form.busy">
                    <span v-if="form.busy"><i class="fa fa-spin fa-spinner"></i></span>
                    <span v-else>{{ saveButtonTitle }}</span>
                </b-btn>
                <b-btn variant="default" @click="cancel()" :disabled="form.busy">Cancel</b-btn>
            </div>
        </div>
    </div>
</template>

<script>
    import FormatsNumbers from "../../../mixins/FormatsNumbers";
    import FormatsDates from "../../../mixins/FormatsDates";
    import Constants from "../../../mixins/Constants";
    import { Decimal } from 'decimal.js';
    import { mapGetters } from 'vuex';

    export default {
        mixins: [ Constants, FormatsNumbers, FormatsDates ],
        props: {
            item: {
                type: Object,
                default: () => {},
            },
        },

        data() {
            return {
                form: new Form({
                    claimable_type: '',
                    // common data
                    rate: 0.00,
                    units: 1,
                    amount: 0.00,
                    client_reload: false,
                    client_id: '',
                    client_first_name: '',
                    client_last_name: '',

                    // expense data
                    name: '',
                    notes: '',
                    date: moment().format('MM/DD/YYYY'),

                    // service data
                    shift_id: '',
                    caregiver_reload: false,
                    caregiver_id: '',
                    caregiver_first_name: '',
                    caregiver_last_name: '',
                    caregiver_gender: '',
                    caregiver_dob: '',
                    caregiver_ssn: '',
                    has_caregiver_ssn: '',
                    caregiver_medicaid_id: '',
                    address1: '',
                    address2: '',
                    city: '',
                    state: '',
                    zip: '',
                    latitude: '',
                    longitude: '',
                    // scheduled_start_time: '',
                    // scheduled_end_time: '',
                    // visit_start_time: '',
                    // visit_end_time: '',
                    evv_start_time: '',
                    evv_end_time: '',
                    checked_in_number: '',
                    checked_out_number: '',
                    checked_in_latitude: '',
                    checked_in_longitude: '',
                    checked_out_latitude: '',
                    checked_out_longitude: '',
                    has_evv: false,
                    evv_method_in: null,
                    evv_method_out: null,
                    service_id: '',
                    service_name: '',
                    service_code: '',
                    activities: '',
                    caregiver_comments: '',

                    shift_start_date: moment().format('MM/DD/YYYY'),
                    shift_end_date: moment().format('MM/DD/YYYY'),
                    shift_start_time: '12:00',
                    shift_end_time: '13:00',
                    service_start_date: moment().format('MM/DD/YYYY'),
                    service_start_time: '12:00',

                    client_dob: '',
                    client_medicaid_id: '',
                    client_medicaid_diagnosis_codes: '',
                    client_program_number: '',
                    client_cirts_number: '',
                    client_ltci_policy_number: '',
                    client_ltci_claim_number: '',
                    client_case_manager: '',
                    client_hic: '',
                    client_invoice_notes: '',

                    client_signature_id: '',
                    caregiver_signature_id: '',
                    is_overtime: false,
                }),
            };
        },

        computed: {
            ...mapGetters({
                claim: 'claims/claim',
                caregivers: 'claims/caregiverList',
                clients: 'filters/clientList',
                services: 'claims/serviceList',
            }),
            saveButtonTitle() {
                return this.item.id ? 'Save Changes' : 'Create Item';
            }
        },

        async mounted() {
            this.$store.commit('filters/setBusiness', this.claim.business_id);
            await this.$store.dispatch('filters/fetchResources', ['clients']);
        },

        methods: {
            save() {
                if (this.item.id) {
                    this.form.patch(`/business/claims/${this.claim.id}/item/${this.item.id}`)
                        .then(({data}) => {
                            this.$store.commit('claims/setClaim', data.data);
                            this.$emit('close');
                        })
                        .catch(() => {
                        });
                } else {
                    this.form.post(`/business/claims/${this.claim.id}/item`)
                        .then( ({ data }) => {
                            this.$store.commit('claims/setClaim', data.data);
                            this.$emit('close');
                        })
                        .catch(() => {
                        });
                }
            },

            cancel() {
                this.$emit('close');
            },

            recalculateAmount() {
                let rate = new Decimal(this.form.rate || 0);
                let units = new Decimal(this.form.units || 0);
                this.form.amount = rate.times(units).toFixed(2);
            },

            recalculateRate() {
                let amount = new Decimal(this.form.amount || 0);
                let units = new Decimal(this.form.units || 0);

                if (amount === new Decimal(0)) {
                    this.form.rate = 0.00;
                    return;
                }

                this.form.rate = amount.dividedBy(units).toFixed(2);
            },

            onChangeCaregiver() {
                this.form.caregiver_reload = true;
                this.form.caregiver_first_name = '';
                this.form.caregiver_last_name = '';
                this.form.caregiver_medicaid_id = '';
                this.form.caregiver_dob = '';
                this.form.caregiver_gender = '';
                this.form.caregiver_ssn = '';
            },

            onChangeClient() {
                this.form.client_reload = true;
                this.form.client_first_name = '';
                this.form.client_last_name = '';
                this.form.client_dob = '';
                this.form.client_medicaid_id = '';
                this.form.client_medicaid_diagnosis_codes = '';
                this.form.client_program_number = '';
                this.form.client_cirts_number = '';
                this.form.client_ltci_policy_number = '';
                this.form.client_ltci_claim_number = '';
                this.form.client_case_manager = '';
            },

            onChangeService(id) {
                let service = this.services.find(x => x.id == id);
                if (! service) {
                    return;
                }

                this.form.service_name = service.name;
                this.form.service_code = service.code;
            },
        },

        watch: {
            claim(newVal, oldVal) {
                if (newVal.business_id != oldVal.business_id) {
                    this.$store.commit('filters/setBusiness', newVal.business_id);
                    this.$store.dispatch('filters/fetchResources', ['clients']);
                }
            },
            item(val) {
                this.form.reset(true);
                if (val.id) {
                    this.form.fill({
                        ...val,
                        ...val.claimable,
                        date: this.formatDate(val.claimable.date),
                    });
                } else {
                    this.form.id = null;
                    this.form.claimable_type = this.CLAIMABLE_TYPES.SERVICE;
                }
            },
        },
    }
</script>
