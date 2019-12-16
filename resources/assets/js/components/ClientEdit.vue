<template>
    <b-card header="Profile"
        header-bg-variant="info"
        header-text-variant="white"
        >
        <loading-card v-if="loading" text="Loading profile..."></loading-card>
        <form v-else @submit.prevent="saveProfile()" @keydown="form.clearError($event.target.name)">
            <b-row>
                <b-col lg="6">
                    <b-form-group label="First Name" label-for="firstname" label-class="required">
                        <b-form-input
                            id="firstname"
                            name="firstname"
                            type="text"
                            v-model="form.firstname"
                            required
                        >
                        </b-form-input>
                        <input-help :form="form" field="firstname" text="Enter their first name."></input-help>
                    </b-form-group>
                    <b-form-group label="Last Name" label-for="lastname" label-class="required">
                        <b-form-input
                            id="lastname"
                            name="lastname"
                            type="text"
                            v-model="form.lastname"
                            required
                            >
                        </b-form-input>
                        <input-help :form="form" field="lastname" text="Enter their last name."></input-help>
                    </b-form-group>
                    <b-form-group label="Client Type" label-for="client_type" label-class="required">
                        <client-type-dropdown
                            id="client_type"
                            name="client_type"
                            v-model="form.client_type"
                        />
                        <input-help :form="form" field="client_type" text="Select the type of payment the client will use."></input-help>
                        <b-form-text class="">NOTE: Changing the client type will change the 1099 settings.</b-form-text>
                    </b-form-group>
                    <b-form-group label="Client Services Coordinator" label-for="case_manager">
                        <b-form-select
                                v-model="form.case_manager_id"
                                id="case_manager_id"
                                name="case_manager_id"
                                class="mr-2 mb-2"
                        >
                            <option :value="null">-- Client Services Coordinator --</option>
                            <option :value="cm.id" v-for="cm in caseManagers" :key="cm.id">{{ cm.name }}</option>
                        </b-form-select>
                        <input-help :form="form" field="case_manager_id" text="Select service coordinator for the client."></input-help>
                    </b-form-group>
                    <b-form-group label="Salesperson">
                        <b-form-select v-model="form.sales_person_id">
                            <option :value="null">None</option>
                            <option v-for="item in salesPeople" :value="item.id" :key="item.id">
                                {{ item.firstname }} {{ item.lastname }}{{ item.active == 1 ? '' : ' (Inactive)'}}
                            </option>
                        </b-form-select>
                        <input-help :form="form" field="sales_person_id"></input-help>
                    </b-form-group>
                    <b-form-group label="Client Status Alias">
                        <b-form-select :options="statusAliasOptions" name="status_alias_id" v-model="form.status_alias_id">
                            <option value="">{{ active ? 'Active' : 'Inactive' }}</option>
                        </b-form-select>
                        <input-help :form="form" field="status_alias_id" :text="showStatusHelp"></input-help>
                    </b-form-group>
                    <business-location-form-group v-model="form.business_id"
                                                  :form="form"
                                                  field="business_id"
                                                  help-text="Select the office location for this client" />
                    <b-form-group label="Gender">
                        <b-form-radio-group id="gender" v-model="form.gender">
                            <b-form-radio value="M">Male</b-form-radio>
                            <b-form-radio value="F">Female</b-form-radio>
                        </b-form-radio-group>
                        <input-help :form="form" field="gender"></input-help>
                    </b-form-group>
                    <b-form-group label="Date of Birth" label-for="date_of_birth">
                        <mask-input v-model="form.date_of_birth" id="date_of_birth" type="date"></mask-input>
                        <input-help :form="form" field="date_of_birth" text="Enter their date of birth. Ex: MM/DD/YYYY"></input-help>
                    </b-form-group>
                </b-col>
                <b-col lg="6">
                    <b-form-group label="Email Address" label-for="email">
                        <b-row>
                            <b-col cols="8">
                                <b-form-input
                                    id="email"
                                    name="email"
                                    type="email"
                                    @blur.native="copyEmailToUsername()"
                                    v-model="form.email"
                                    :disabled="form.no_email"
                                >
                                </b-form-input>
                            </b-col>
                            <b-col cols="4">
                                <div class="form-check">
                                    <label class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" name="no_email"
                                               v-model="form.no_email" value="1" @input="toggleNoEmail()">
                                        <span class="custom-control-indicator"></span>
                                        <span class="custom-control-description">No Email</span>
                                    </label>
                                </div>
                            </b-col>
                        </b-row>
                        <input-help :form="form" field="email"
                                    text="Enter their email address or check the box if client does not have an email. Ex: user@domain.com"></input-help>
                    </b-form-group>
                    <b-form-group label="Username" label-for="username">
                        <b-row>
                            <b-col cols="8">
                                <b-form-input
                                        id="username"
                                        name="username"
                                        type="text"
                                        v-model="form.username"
                                        :disabled="form.no_username"
                                >
                                </b-form-input>
                            </b-col>
                            <b-col cols="4">
                                <div class="form-check">
                                    <label class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" name="no_username"
                                            v-model="form.no_username" value="1" @input="toggleNoUsername()">
                                        <span class="custom-control-indicator"></span>
                                        <span class="custom-control-description">Let Client Choose</span>
                                    </label>
                                </div>
                            </b-col>
                        </b-row>
                        <input-help :form="form" field="username" text="Enter their username to be used for logins."></input-help>
                    </b-form-group>
                    <!-- <b-form-group label="Username" label-for="username" label-class="required">
                        <b-form-input
                                id="username"
                                name="username"
                                type="text"
                                v-model="form.username"
                                :disabled="form.no_username"
                        >
                        </b-form-input>
                        <input-help :form="form" field="username" text="Enter their username to be used for logins."></input-help>
                    </b-form-group> -->
                    <b-form-group label="Social Security Number" label-for="ssn">
                        <mask-input v-model="form.ssn" id="ssn" name="ssn" type="ssn"></mask-input>
                        <input-help :form="form" field="ssn" text="Enter the client's social security number."></input-help>
                    </b-form-group>
                    <b-form-group label="Photo">
                        <edit-avatar v-model="form.avatar" :size="150" :cropperPadding="100" />
                        <input-help :form="form" field="avatar"></input-help>
                    </b-form-group>
                    <b-form-group label="HIC" label-for="hic">
                        <b-form-input
                            id="hic"
                            name="hic"
                            type="text"
                            v-model="form.hic"
                        >
                        </b-form-input>
                        <input-help :form="form" field="hic" text="Enter their HIC."></input-help>
                    </b-form-group>
                    <b-form-group label="Travel Directions" label-for="travel_directions">
                        <b-form-textarea
                            id="travel_directions"
                            name="travel_directions"
                            rows="3"
                            v-model="form.travel_directions"
                        >
                        </b-form-textarea>
                        <input-help :form="form" field="travel_directions" text="Enter their Travel Directions."></input-help>
                    </b-form-group>
                </b-col>
            </b-row>
            <b-row>
                <b-col lg="6">
                    <b-form-group label="Date inquired about Service">
                        <date-picker id="inquiry_date" v-model="form.inquiry_date"></date-picker>
                        <input-help :form="form" field="inquiry_date"></input-help>
                    </b-form-group>

                    <b-form-group class="mb-2">
                        <business-referral-source-select :businessId=" client.business_id " v-model="form.referral_source_id" source-type="client"></business-referral-source-select>
                        <div class="d-flex justify-content-end">
                            <input-help :form="form" field="referral_source_id" text="Enter how the prospect was referred."/>
                        </div>
                    </b-form-group>

                    <b-form-group>
                        <b-form-checkbox id="ambulatory"
                                         v-model="form.ambulatory"
                                         :value="true"
                                         :unchecked-value="false">
                            Ambulatory
                        </b-form-checkbox>
                    </b-form-group>

                    <b-form-group v-if="businessSendsSummaryEmails">
                        <div class="form-check">
                            <label class="custom-control custom-checkbox">
                                <input type="checkbox"
                                    class="custom-control-input"
                                    name="receive_summary_email"
                                    v-model="form.receive_summary_email"
                                    :true-value="1"
                                    :false-value="0"
                                    :disabled="authInactive">
                                <span class="custom-control-indicator"></span>
                                <span class="custom-control-description">Receive the weekly Visit Summary with Pending Charges email</span>
                            </label>
                            <input-help :form="form" field="receive_summary_email" text="An example of this email can be found under Settings > General > Shift Confirmations" class="ml-4"></input-help>
                        </div>
                    </b-form-group>
                </b-col>
            </b-row>
            <!------------------------------------->
            <b-row >
                <b-col lg="3" v-if="authRole === 'admin' || client.lock_1099 === 1">
                    <b-form-group label="Caregiver 1099" :label-class="required">
                        <b-form-select v-model="form.send_1099" :required="required">
                            <option value="choose">Select an Option</option>
                            <option value="yes">Yes</option>
                            <option value="no">No</option>
                        </b-form-select>
                    </b-form-group>
                </b-col>
                <b-col lg="3" v-if="authRole === 'admin' || client.lock_1099 === 1">
                    <b-form-group label="Payer">
                        <b-radio-group v-model="form.caregiver_1099" stacked :required="required">
                            <b-radio value="client">Send on Client's Behalf</b-radio>
                            <b-radio value="ally">Send on Ally's Behalf</b-radio>
                        </b-radio-group>
                    </b-form-group>
                </b-col>
                <b-col lg="6" v-if="authRole !== 'admin' || client.lock_1099 === 0">
                    <b-form-group label="Caregiver 1099">
                        <label>
                            1099s are being sent on behalf of {{ payerLabel }}. Contact Ally if you wish to change this.
                        </label>
                    </b-form-group>
                </b-col>
            </b-row>
            <!------------------------------------->
            <b-row>
                <b-col lg="6">
                    <b-form-group label="Service Start Date">
                        <date-picker id="service_start_date" v-model="form.service_start_date"></date-picker>
                        <input-help :form="form" field="service_start_date"></input-help>
                    </b-form-group>
                    <b-form-group label="Diagnosis">
                        <b-form-input id="diagnosis" v-model="form.diagnosis"></b-form-input>
                        <input-help :form="form" field="diagnosis"></input-help>
                    </b-form-group>
                    <b-form-group label="Disaster Code Plan" label-for="disaster_code_plan">
                        <b-select name="disaster_code_plan" id="disaster_code_plan" v-model="form.disaster_code_plan">
                            <option value="">-- Select a Disaster Code Plan --</option>
                            <option v-for="item in disasterCodes" :key="item" :value="item">{{ item }}</option>
                        </b-select>
                        <input-help :form="form" field="disaster_code_plan" text="Select their Disaster Code Plan."></input-help>
                    </b-form-group>
                    <b-form-group label="Disaster Planning" label-for="disaster_planning">
                        <b-form-textarea
                            id="disaster_planning"
                            name="disaster_planning"
                            rows="3"
                            v-model="form.disaster_planning"
                        >
                        </b-form-textarea>
                        <input-help :form="form" field="disaster_planning" text="Enter their Disaster Planning."></input-help>
                    </b-form-group>
                </b-col>
            </b-row>
            <b-row>
                <b-col lg="6">
                    <b-form-group label="Created On" label-for="created_at">
                        <b-form-input
                            id="created_at"
                            name="created_at"
                            type="text"
                            v-model="form.created_at"
                            readonly
                        >
                        </b-form-input>
                    </b-form-group>
                    <b-form-group label="Created By" label-for="created_by">
                        <b-form-input
                            id="created_by"
                            name="created_by"
                            type="text"
                            v-model="form.created_by"
                            readonly
                        >
                        </b-form-input>
                    </b-form-group>
                </b-col>
                <b-col lg="6">
                    <b-form-group label="Modified On" label-for="modified_at">
                        <b-form-input
                            id="modified_at"
                            name="modified_at"
                            type="text"
                            v-model="form.modified_at"
                            readonly
                        >
                        </b-form-input>
                    </b-form-group>
                    <b-form-group label="Modified By" label-for="modified_by">
                        <b-form-input
                            id="modified_by"
                            name="modified_by"
                            type="text"
                            v-model="form.modified_by"
                            readonly
                        >
                        </b-form-input>
                    </b-form-group>
                </b-col>
            </b-row>
            <b-row>
                <b-col lg="12">
                    <hr />
                </b-col>
                <b-col lg="6">
                    <b-form-group label="Ally Client Agreement Status" label-for="agreement_status">
                        <b-form-select
                                id="agreement_status"
                                name="agreement_status"
                                v-model="form.agreement_status"
                                :disabled="form.agreement_status == 'electronic'"
                        >
                            <option value="">--Please Select--</option>
                            <option v-if="hiddenOnboardStatuses[form.agreement_status]" :value="form.agreement_status">{{ hiddenOnboardStatuses[form.agreement_status] }}</option>
                            <option v-for="(display, value) in onboardStatuses" :value="value" :key="value">{{ display }}</option>
                        </b-form-select>
                        <input-help :form="form" field="agreement_status" :text="onboardStatusText"></input-help>
                    </b-form-group>
                </b-col>
                <b-col lg="6">
                    <div class="mb-3">
                        <div>
                            <label for="agreement_status" class="col-form-label pt-0">Account Setup Status: 
                                <span v-if="! client.setup_status" class="text-danger">Not Started</span>
                                <span v-if="['accepted_terms', 'created_account'].includes(client.setup_status)" class="text-warning">In Progress</span>
                                <span v-if="client.setup_status == 'added_payment'" class="text-success">Complete</span>
                            </label>
                        </div>
                        <div>
                            <span class="mr-2"><i :class="setupCheckClass('accepted_terms')" aria-hidden="true"></i> Agreed to Terms</span>
                            <span class="mr-2"><i :class="setupCheckClass('created_account')" aria-hidden="true"></i> Created Login</span>
                            <span class="mr-2"><i :class="setupCheckClass('added_payment')" aria-hidden="true"></i> Added Payment Method</span>
                        </div>
                    </div>
                    <b-form-group label="Account Setup URL">
                        <a :href="client.setup_url" target="_blank">{{ client.setup_url }}</a>
                        <small class="form-text text-muted">The URL the client can use to setup their account.</small>
                    </b-form-group>

                    <div>
                        <label class="col-form-label pt-0"><strong>Welcome Email Last Sent:</strong> 
                            <span>{{ client.user.welcome_email_sent_at ? formatDateTimeFromUTC(client.user.welcome_email_sent_at) : 'Never' }}</span>
                        </label>
                    </div>
                    <div>
                        <label class="col-form-label pt-0"><strong>Training Email Last Sent:</strong> 
                            <span>{{ client.user.training_email_sent_at ? formatDateTimeFromUTC(client.user.training_email_sent_at) : 'Never' }}</span>
                        </label>
                    </div>

                    <b-button variant="info"
                        type="button"
                        :disabled="sendingWelcomeEmail"
                        @click.prevent="sendWelcomeEmail()"
                    >
                        <i class="fa fa-spinner fa-spin" v-if="sendingWelcomeEmail"></i>
                        Send Welcome Email
                    </b-button>

                    <b-button variant="info"
                        type="button"
                        :disabled="sendingTrainingEmail"
                        @click.prevent="sendTrainingEmail()"
                    >
                        <i class="fa fa-spinner fa-spin" v-if="sendingTrainingEmail"></i>
                        Send Training Email
                    </b-button>

<!--                    <b-button v-if="client.onboarding_step < 6" @click="startOnboarding()" variant="info">-->
<!--                        Start Client Onboarding-->
<!--                    </b-button>-->
                </b-col>
            </b-row>
            <b-row>
                <b-col lg="12" class="mt-4">
                    <b-button variant="success" type="submit">Save Profile</b-button>
                    <b-button variant="primary" @click="passwordModal = true"><i class="fa fa-lock"></i> Reset Password</b-button>
                    <b-button variant="danger" @click="$refs.deactivateClientModal.show()" v-if="active"><i class="fa fa-times"></i> Deactivate Client</b-button>
                    <template v-else>
                        <b-button variant="info" @click="activateModal = true"><i class="fa fa-refresh"></i> Re-activate Client</b-button>
                        <b-button variant="info" @click=" getDischarge() "><i class="fa fa-file mr-1"></i>Download Discharge Summary</b-button>
                    </template>
                </b-col>
            </b-row>
        </form>

        <reset-password-modal v-model="passwordModal" :url="'/business/clients/' + this.client.id + '/password'"></reset-password-modal>

        <deactivate-client-modal :client="client" ref="deactivateClientModal"></deactivate-client-modal>

        <b-modal id="activateModal"
            title="Are you sure?"
            @ok="reactivateClient"
            v-model="activateModal">
                Are you sure you wish to re-activate {{ this.client.name }}?
        </b-modal>

        <!-- <client-referral-modal @saved="newrefsourcedata" v-model="showReferralModal" :source="{}"></client-referral-modal> -->

        <business-referral-source-modal
            @saved="savedReferralSource"
            v-model="showReferralModal"
            :source="{}"
            source-type="client"
        ></business-referral-source-modal>

        <discharge-summary-modal ref="dischargeSummaryModal" :client="client"></discharge-summary-modal>

        <b-modal v-model="onboardingWarning" title="Not Available">
            Contact Ally support to configure this feature.
        </b-modal>

        <confirm-modal title="Send Welcome Email" ref="confirmWelcomeEmail" yesButton="Send Email">
            <p>Send welcome email to {{ client.email }}?</p><br />
            <p>This will send {{ client.name }} an email instructing them to click on a private link to confirm their information and reset their password.</p>
        </confirm-modal>

        <confirm-modal title="Send Training Email" ref="confirmTrainingEmail" yesButton="Send Email">
            <p>Send training email to {{ client.email }}?</p><br />
            <p>This will send {{ client.name }} an email linking them to the Knowledge Base.</p>
        </confirm-modal>
    </b-card>
</template>

<script>
    import ClientForm from '../mixins/ClientForm';
    import DatePicker from './DatePicker';
    import FormatsDates from '../mixins/FormatsDates';
    import BusinessLocationSelect from './business/BusinessLocationSelect';
    import BusinessLocationFormGroup from "./business/BusinessLocationFormGroup";
    import DeactivateClientModal from './modals/DeactivateClientModal';
    import DischargeSummaryModal from './modals/DischargeSummaryModal';
    import AuthUser from '../mixins/AuthUser';
    import Constants from '../mixins/Constants';

    window.croppie = require('croppie');

    export default {
        props: {
            'client': {},
            'lastStatusDate' : {},
            'referralsources': {},
            salesPeople: {
                type: Array,
                required: true
            }
        },

        mixins: [ClientForm, FormatsDates, AuthUser, Constants],

        components: {
            BusinessLocationFormGroup,
            DatePicker,
            BusinessLocationSelect,
            DeactivateClientModal,
            DischargeSummaryModal
        },

        data() {
            return {
                form: new Form({
                    firstname: this.client.firstname,
                    lastname: this.client.lastname,
                    email: this.client.email,
                    no_email: false,
                    username: this.client.username,
                    no_username: false,
                    date_of_birth: (this.client.date_of_birth) ? this.formatDate(this.client.date_of_birth) : null,
                    client_type: this.client.client_type,
                    ssn: (this.client.hasSsn) ? '***-**-****' : '',
                    agreement_status: this.client.agreement_status,
                    inquiry_date: this.client.inquiry_date ? this.formatDate(this.client.inquiry_date) : '',
                    service_start_date: this.client.service_start_date ? this.formatDate(this.client.service_start_date) : '',
                    referral_source_id: this.client.referral_source_id ? this.client.referral_source_id : "",
                    diagnosis: this.client.diagnosis,
                    ambulatory: !!this.client.ambulatory,
                    gender: this.client.gender,
                    hospital_name: this.client.hospital_name,
                    hospital_number: this.client.hospital_number,
                    avatar: this.client.avatar,
                    business_id: this.client.business_id,
                    case_manager_id: this.client.case_manager_id,
                    hic: this.client.hic,
                    travel_directions: this.client.travel_directions,
                    caregiver_1099: this.client.caregiver_1099,
                    disaster_code_plan: this.client.disaster_code_plan,
                    disaster_planning: this.client.disaster_planning,
                    created_by: this.client.creator && this.client.creator.nameLastFirst,
                    created_at: this.formatDateTime(this.client.created_at.date),
                    modified_by: this.client.updator && this.client.updator.nameLastFirst,
                    modified_at: this.formatDateTime(this.client.updated_at.date),
                    receive_summary_email: this.client.receive_summary_email,
                    sales_person_id: this.client.sales_person_id,
                    status_alias_id: this.client.status_alias_id || '',
                    send_1099: this.client.send_1099,
                }),
                passwordModal: false,
                active: this.client.active,
                deactivateModal: false,
                activateModal: false,
                showReferralModal: false,
                caseManagers: [],
                sendEmailModal: false,
                statusAliases: [],
                localLastStatusDate: null,
                onboardingWarning: false,
                loading: false,
                sendingTrainingEmail: false,
                sendingWelcomeEmail: false,
                payerLabel: '',
                errors1099: '',
                required: (this.client_send == 'choose') ? 'required' : false,
            }
        },

        async mounted() {
            this.loading = true;
            this.localLastStatusDate = this.lastStatusDate;
            this.checkForNoEmailDomain();
            this.checkForNoUsername();
            await this.loadOfficeUsers();
            await this.fetchStatusAliases();

            this.payerTypeLabel();

            this.loading = false;
        },

        methods: {

            getDischarge(){
                // this may be named very inappropriately..

                window.open( `/business/clients/discharge-letter/${this.client.id}` );
            },

            canSendEmails() {
                if (! this.form.email || this.isEmptyEmail(this.form.email)) {
                    alert('You cannot send any emails to this user because there is no email associated with their account.');
                    return false;
                }
                return true;
            },

            sendWelcomeEmail() {
                if (! this.canSendEmails()) {
                    return;
                }
                this.$refs.confirmWelcomeEmail.confirm(() => {
                    this.sendingWelcomeEmail = true;
                    let form = new Form({});
                    form.post(`/business/clients/${this.client.id}/welcome-email`)
                        .then(response => {
                        })
                        .catch( e => {
                        })
                        .finally(() => {
                            this.sendingWelcomeEmail = false;
                        });
                });
            },

            sendTrainingEmail() {
                if (! this.canSendEmails()) {
                    return;
                }
                this.$refs.confirmTrainingEmail.confirm(() => {
                    this.sendingTrainingEmail = true;
                    let form = new Form({});
                    form.post(`/business/clients/${this.client.id}/training-email`)
                        .then(response => {
                        })
                        .catch( e => {
                        })
                        .finally(() => {
                            this.sendingTrainingEmail = true;
                        });
                });
            },

            setupCheckClass(step) {
                let check = false;
                switch (step) {
                    case 'accepted_terms':
                        check = ['accepted_terms', 'created_account', 'added_payment'].includes(this.client.setup_status);
                        break;
                    case 'created_account':
                        check = ['created_account', 'added_payment'].includes(this.client.setup_status);
                        break;
                    case 'added_payment':
                        check = ['added_payment'].includes(this.client.setup_status);
                        break;
                }

                return check ? 'fa fa-check-square-o' : 'fa fa-square-o';
            },

            savedReferralSource(data) {
                if(data) {
                    this.showReferralModal = false;
                    this.referralsources.push(data);
                    this.form.referral_source_id = data.id;
                }
            },

            async loadOfficeUsers() {
                const response = await axios.get(`/business/${this.client.business_id}/office-users`);
                this.caseManagers = response.data;
            },

            checkForNoEmailDomain() {
                if (this.form.email) {
                    if (this.isEmptyEmail(this.form.email)) {
                        this.form.no_email = true;
                        this.form.email = null;
                    }
                }
            },

            isEmptyEmail(email) {
                let domain = 'noemail.allyms.com';
                if (email.substr(domain.length * -1) === domain) {
                    return true;
                }
                return false;
            },

            checkForNoUsername() {
                if (this.form.username) {
                    if (this.form.username.substr(0, 9) == 'no_login_') {
                        this.form.no_username = true;
                        this.form.username = null;
                    }
                }
            },

            reactivateClient() {
                let form = new Form();
                form.post('/business/clients/' + this.client.id + '/reactivate')
                    .then(response => this.active = 1);
            },

            async saveProfile() {

                if(this.show1099Warning){
                    let message = "This Client is set to receive a year end 1099 but is missing some required information. Please check their Name, SSN, and Address Fields.";
                    if (! confirm(message)) { return ; }
                }

                await this.form.patch('/business/clients/' + this.client.id)
                    .then( ({ data }) => {
                        this.form.avatar = data.data.avatar;
                        if (this.form.ssn) {
                            this.form.ssn = '***-**-****';
                        }
                        if (this.form.wasModified('agreement_status')) {
                            this.client.agreement_status = this.form.agreement_status;
                            this.localLastStatusDate = moment.utc().format();
                        }
                    })
                    .catch(e => {});
            },

            startOnboarding() {
                if (this.business.enable_client_onboarding) {
                    window.location = `/business/clients/${this.client.id}/onboarding`
                } else {
                    this.onboardingWarning = true;
                }
            },

            async fetchStatusAliases() {
                let response = await axios.get(`/business/status-aliases?business_id=${this.client.business_id}`);
                if (response.data && response.data.client) {
                    this.statusAliases = response.data;
                } else {
                    this.statusAliases = {caregiver: [], client: []};
                }
            },

            toggleNoEmail() {
                if (this.form.no_email) {
                    this.form.email = '';
                }
            },

            toggleNoUsername() {
                if (this.form.no_username) {
                    this.form.username = '';
                }
            },

            copyEmailToUsername() {
                if (this.form.no_username === true) {
                    return;
                }
                if (this.form.email && !this.form.username) {
                    this.form.username = this.form.email;
                }
            },

            payerTypeLabel(){
                if(this.form.caregiver_1099 === 'client' || this.form.caregiver_1099 === ''){
                    this.payerLabel = 'Client';
                }else if(this.form.caregiver_1099 === 'ally' || this.form.caregiver_1099 === 'ally_locked'){
                    this.payerLabel = 'Ally';
                }
            },

        },

        computed: {
            business() {
                return this.client.business_id ? this.$store.getters.getBusiness(this.client.business_id) : {};
            },

            businessSendsSummaryEmails() {
                return !! this.business.shift_confirmation_email;
            },

            lastStatusUpdated() {
                return moment.utc(this.localLastStatusDate).local().format('L') + ' at ' + moment.utc(this.localLastStatusDate).local().format('LT');
            },

            onboardStatusText() {
                if (this.localLastStatusDate) {
                    switch (this.form.agreement_status) {
                        case 'paper': // paper signature
                            return 'Signed: ' + this.lastStatusUpdated;
                        case 'electronic': // electronic signature
                            return 'Signed Electronically: ' + this.lastStatusUpdated;
                    }
                    return 'The status was last updated ' + this.lastStatusUpdated;
                }
                return 'Select the Ally Agreement status of the client.';
            },

            statusAliasOptions() {
                if (! this.statusAliases || !this.statusAliases.client) {
                    return [];
                }

                return this.statusAliases.client.filter(item => {
                    return item.active == this.active;
                }).map(item => {
                    return {
                        value: item.id,
                        text: item.name,
                    };
                });
            },

            showStatusHelp() {
                return "Note: To set this client to an " + (this.client.active ? 'inactive': 'active') + " status, use the " + (this.client.active ? 'Deactivate' : 'Re-activate') + " Client button below.";
            },

            disasterCodes() {
                return ['1A', '1B', '1C', '1D', '1E', '1H', '1S', '2A', '2B', '2C', '2D', '2E', '2H', '2S', '3A', '3B', '3C', '3D', '3E', '3H', '3S', '4A', '4B', '4C', '4D', '4E', '4H', '4S'];
            },

            show1099Warning()
            {
                if( this.form.send_1099 === 'yes'){
                    let address = this.client.addresses[0];

                    if(address.address1 && address.city && address.state && address.zip && this.client.ssn){
                        return false;
                    }
                }
                return true;
            },
        },
        watch: {
        }
    }
</script>

<style scoped>
    .pad-top {
        padding-top: 16px;
    }
</style>
