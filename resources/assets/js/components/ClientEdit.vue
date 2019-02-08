<template>
    <b-card header="Profile"
        header-bg-variant="info"
        header-text-variant="white"
        >
        <form @submit.prevent="saveProfile()" @keydown="form.clearError($event.target.name)">
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
                        <b-form-select
                                id="client_type"
                                name="client_type"
                                v-model="form.client_type"
                        >
                            <option value="">--Select--</option>
                            <option value="private_pay">Private Pay</option>
                            <option value="medicaid">Medicaid</option>
                            <option value="VA">VA</option>
                            <option value="LTCI">LTC Insurance</option>
                        </b-form-select>
                        <input-help :form="form" field="client_type" text="Select the type of payment the client will use."></input-help>
                    </b-form-group>
                    <b-form-group label="Case Manager" label-for="case_manager">
                        <b-form-select 
                                v-model="form.case_manager_id" 
                                id="case_manager_id"
                                name="case_manager_id"
                                class="mr-2 mb-2"
                        >
                            <option :value="null">-- Case Manager --</option>
                            <option :value="cm.id" v-for="cm in caseManagers" :key="cm.id">{{ cm.name }}</option>
                        </b-form-select>
                        <input-help :form="form" field="case_manager_id" text="Select case manager for the client."></input-help>
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
                    </b-form-group>
                    <b-form-group label="Date of Birth" label-for="date_of_birth">
                        <mask-input v-model="form.date_of_birth" id="date_of_birth" type="date"></mask-input>
                        <input-help :form="form" field="date_of_birth" text="Enter their date of birth. Ex: MM/DD/YYYY"></input-help>
                    </b-form-group>
                    <b-form-group label="Email Address" label-for="email">
                        <b-row>
                            <b-col cols="8">
                                <b-form-input
                                        id="email"
                                        name="email"
                                        type="email"
                                        v-model="form.email"
                                        :disabled="form.no_email"
                                >
                                </b-form-input>
                            </b-col>
                            <b-col cols="4">
                                <div class="form-check">
                                    <label class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" name="no_email"
                                               v-model="form.no_email" value="1">
                                        <span class="custom-control-indicator"></span>
                                        <span class="custom-control-description">No Email</span>
                                    </label>
                                </div>
                            </b-col>
                        </b-row>
                        <input-help :form="form" field="email"
                                    text="Enter their email address or check the box if client does not have an email. Ex: user@domain.com"></input-help>
                    </b-form-group>
                </b-col>
                <b-col lg="6">
                    <b-form-group label="Username" label-for="username" label-class="required">
                        <b-form-input
                                id="username"
                                name="username"
                                type="text"
                                v-model="form.username"
                        >
                        </b-form-input>
                        <input-help :form="form" field="username" text="Enter their username to be used for logins."></input-help>
                    </b-form-group>
                    <b-form-group label="Social Security Number" label-for="ssn">
                        <mask-input v-model="form.ssn" id="ssn" name="ssn" type="ssn"></mask-input>
                        <input-help :form="form" field="ssn" text="Enter the client's social security number."></input-help>
                    </b-form-group>
                    <b-form-group label="Photo">
                        <edit-avatar v-model="form.avatar" :size="150" :cropperPadding="100" />
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
                    </b-form-group>

                    <b-form-group>
                        <business-referral-source-select v-model="form.referral_source_id" source-type="client"></business-referral-source-select>
                        <input-help :form="form" field="referred_by" text="Enter how the prospect was referred." />
                    </b-form-group>

                    <b-form-group>
                        <b-form-checkbox id="ambulatory"
                                         v-model="form.ambulatory"
                                         :value="true"
                                         :unchecked-value="false">
                            Ambulatory
                        </b-form-checkbox>
                    </b-form-group>

                    <b-form-group>
                        <b-form-checkbox id="caregiver_1099"
                                         v-model="form.caregiver_1099"
                                         :value="true"
                                         :unchecked-value="false">
                            Send 1099 to caregivers on the client’s behalf
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
                <b-col lg="6">
                    <b-form-group label="Service Start Date">
                        <date-picker id="service_start_date" v-model="form.service_start_date"></date-picker>
                    </b-form-group>
                    <b-form-group label="Diagnosis">
                        <b-form-input id="diagnosis" v-model="form.diagnosis"></b-form-input>
                    </b-form-group>
                    <b-form-group label="Disaster Code Plan" label-for="disaster_code_plan">
                        <b-form-input
                            id="disaster_code_plan"
                            name="disaster_code_plan"
                            type="text"
                            v-model="form.disaster_code_plan"
                        >
                        </b-form-input>
                        <input-help :form="form" field="disaster_code_plan" text="Enter their Disaster Code Plan."></input-help>
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
                <b-col>
                    <p class="h6">Preferences</p>
                    <hr>
                </b-col>
            </b-row>

            <b-row>
                <b-col lg="6">
                    <b-form-group label="Caregiver Gender" label-for="gender">
                        <b-form-select id="gender"
                                       v-model="preferences.gender"
                        >
                            <option :value="null">No Preference</option>
                            <option value="F">Female</option>
                            <option value="M">Male</option>
                        </b-form-select>
                        <input-help :form="preferences" field="gender" text="" />
                    </b-form-group>
                    <b-form-group label="Caregiver License/Certification" label-for="license">
                        <b-form-select id="license"
                                       v-model="preferences.license"
                        >
                            <option :value="null">No Preference</option>
                            <option value="CNA">CNA</option>
                            <option value="HHA">HHA</option>
                        </b-form-select>
                        <input-help :form="preferences" field="license" text="" />
                    </b-form-group>
                    <b-form-group label="Caregiver's Spoken Language" label-for="language">
                        <b-form-select id="language"
                                       v-model="preferences.language"
                        >
                            <option :value="null">No Preference</option>
                            <option value="en">English</option>
                            <option value="es">Spanish</option>
                            <option value="fr">French</option>
                            <option value="de">German</option>
                        </b-form-select>
                        <input-help :form="preferences" field="language" text="" />
                    </b-form-group>
                </b-col>
                <b-col lg="6">
                    <b-form-group label="Preferred Hospital">
                        <b-form-input id="hospital_name"
                                      v-model="form.hospital_name"></b-form-input>
                    </b-form-group>
                    <b-form-group label="Hospital Phone Number">
                        <b-form-input id="hospital_number"
                                      v-model="form.hospital_number"></b-form-input>
                    </b-form-group>
                </b-col>
            </b-row>

            <b-row>
                <b-col lg="12">
                    <hr />
                </b-col>
                <b-col lg="8">
                    <b-row>
                        <b-col lg="6" sm="12">
                            <b-form-group label="Ally Client Agreement Status" label-for="onboard_status">
                                <b-form-select
                                        id="onboard_status"
                                        name="onboard_status"
                                        v-model="form.onboard_status"
                                        :disabled="(form.onboard_status == 'reconfirmed_checkbox' || form.onboard_status == 'agreement_checkbox')"
                                >
                                    <option value="">--Please Select--</option>
                                    <option v-if="hiddenOnboardStatuses[form.onboard_status]" :value="form.onboard_status">{{ hiddenOnboardStatuses[form.onboard_status] }}</option>
                                    <option v-for="(display, value) in onboardStatuses" :value="value" :key="value">{{ display }}</option>
                                </b-form-select>
                                <input-help :form="form" field="onboard_status" :text="onboardStatusText"></input-help>
                            </b-form-group>
                        </b-col>
                        <b-col lg="6" sm="12">
                            <b-form-group v-if="client.onboard_status == 'needs_agreement'">
                                <label class="hidden-sm-down"><span>Client Agreement Email</span></label>
                                <br>
                                <b-button variant="info" @click="sendConfirmation()" size="sm">Send Client Agreement via
                                    Email
                                </b-button>
                            </b-form-group>
                            <b-form-group v-if="client.onboarding_step < 6">
                                <label class="hidden-sm-down"><span>Start Client Onboarding</span></label>
                                <br>
                                <b-button :href="`/business/clients/${client.id}/onboarding`" variant="info" size="sm">Start Client Onboarding</b-button>
                            </b-form-group>
                            <b-form-group v-if="client.onboard_status == 'emailed_reconfirmation'">
                                <label class="hidden-sm-down"><span>Client Agreement Email</span></label>
                                <b-button variant="info" @click="sendConfirmation()" size="sm">Resend Client Agreement via
                                    Email
                                </b-button>
                            </b-form-group>
                        </b-col>
                    </b-row>
                </b-col>
                <b-col lg="4">
                    <b-form-group label="Confirmation URL" label-for="ssn" v-if="confirmUrl && (form.onboard_status=='needs_agreement' || form.onboard_status=='emailed_reconfirmation')">
                        <a :href="confirmUrl" target="_blank">{{ confirmUrl }}</a>
                        <input-help :form="form" field="confirmUrl" text="The URL the client can use to confirm their Ally agreement."></input-help>
                    </b-form-group>
                </b-col>
            </b-row>
            <b-row>
                <b-col lg="12">
                    <b-button variant="success" type="submit">Save Profile</b-button>
                    <b-button variant="primary" @click="passwordModal = true"><i class="fa fa-lock"></i> Reset Password</b-button>
                    <b-button variant="danger" @click="deactivateModal = true" v-if="active"><i class="fa fa-times"></i> Deactivate Client</b-button>
                    <b-button variant="info" @click="activateModal = true" v-else><i class="fa fa-refresh"></i> Re-activate Client</b-button>
                </b-col>
            </b-row>
        </form>

        <reset-password-modal v-model="passwordModal" :url="'/business/clients/' + this.client.id + '/password'"></reset-password-modal>

        <b-modal id="deactivateModal"
                 title="Are you sure?"
                 v-model="deactivateModal"
                 ok-title="OK">

            <b-container>
                <b-row>
                    <b-col lg="12" class="text-center">
                        <div class="mb-3">Are you sure you wish to archive {{ this.client.name }}?</div>
                        <div v-if="client.future_schedules > 0">All <span class="text-danger">{{ this.client.future_schedules }}</span> of their future scheduled shifts will be deleted.</div>
                        <div v-else>They have no future scheduled shifts.</div>

                        <b-form-group slabel-for="inactive_at" class="mt-4">
                            <date-picker
                                class="w-50 mx-auto"
                                v-model="inactive_at"
                                id="inactive_at"
                                placeholder="Inactive Date">
                            </date-picker>
                            <input-help :form="form" field="inactive_at" text="Set a deactivated date (optional)"></input-help>
                        </b-form-group>

                    </b-col>
                </b-row>
            </b-container>
            <div slot="modal-footer">
                <b-btn v-if="client.future_schedules > 0" variant="danger" class="mr-2" @click.prevent="archiveClient">Yes - Delete Future Schedules</b-btn>
                <b-btn v-else variant="danger" class="mr-2" @click.prevent="archiveClient">Yes</b-btn>
               <b-btn variant="default" @click="deactivateModal = false">Cancel</b-btn>
            </div>
        </b-modal>

        <b-modal id="activateModal"
            title="Are you sure?"
            @ok="reactivateClient"
            v-model="activateModal">
                Are you sure you wish to re-activate {{ this.client.name }}?
        </b-modal>

        <business-referral-source-modal 
            @saved="savedReferralSource"
            v-model="showReferralModal"
            :source="{}"
            source-type="client"
        ></business-referral-source-modal>
    </b-card>
</template>

<script>
    import ClientForm from '../mixins/ClientForm';
    import DatePicker from './DatePicker';
    import FormatsDates from '../mixins/FormatsDates';
    import BusinessLocationSelect from './business/BusinessLocationSelect'
    import BusinessLocationFormGroup from "./business/BusinessLocationFormGroup";
    window.croppie = require('croppie');

    export default {
        props: {
            'client': {},
            'lastStatusDate' : {},
            'confirmUrl': {},
            'referralsources': {}
        },

        mixins: [ClientForm, FormatsDates],

        components: {
            BusinessLocationFormGroup,
            DatePicker,
            BusinessLocationSelect,
        },

        data() {
            return {
                form: new Form({
                    firstname: this.client.firstname,
                    lastname: this.client.lastname,
                    email: this.client.email,
                    no_email: false,
                    username: this.client.username,
                    date_of_birth: (this.client.date_of_birth) ? this.formatDate(this.client.date_of_birth) : null,
                    client_type: this.client.client_type,
                    ssn: (this.client.hasSsn) ? '***-**-****' : '',
                    onboard_status: this.client.onboard_status,
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
                    caregiver_1099: !!this.client.caregiver_1099,
                    disaster_code_plan: this.client.disaster_code_plan,
                    disaster_planning: this.client.disaster_planning,
                    created_by: this.client.creator && this.client.creator.nameLastFirst,
                    created_at: this.client.created_at,
                    modified_by: this.client.updator && this.client.updator.nameLastFirst,
                    modified_at: this.client.updated_at,
                    receive_summary_email: this.client.receive_summary_email,
                }),
                preferences: new Form({
                    gender: this.client.preferences ? this.client.preferences.gender : null,
                    license: this.client.preferences ? this.client.preferences.license : null,
                    language: this.client.preferences ? this.client.preferences.language : null,
                }),
                passwordModal: false,
                active: this.client.active,
                deactivateModal: false,
                activateModal: false,
                inactive_at: '',
                showReferralModal: false,
                caseManagers: [],
            }
        },

        mounted() {
            this.checkForNoEmailDomain();
            this.loadOfficeUsers();
        },

        methods: {
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
                let domain = 'noemail.allyms.com';
                if (this.form.email) {
                    if (this.form.email.substr(domain.length * -1) === domain) {
                        this.form.no_email = true;
                        this.form.email = null;
                    }
                }
            },

            archiveClient() {
                let form = new Form();
                form.submit('delete', `/business/clients/${this.client.id}?inactive_at=${this.inactive_at}`);
            },

            reactivateClient() {
                let form = new Form();
                form.post('/business/clients/' + this.client.id + '/reactivate')
                    .then(response => this.active = 1);
            },

            async saveProfile() {
                let response = await this.form.patch('/business/clients/' + this.client.id)
                this.form.avatar = response.data.data.avatar;

                this.preferences.alertOnResponse = false;
                this.preferences.post('/business/clients/' + this.client.id + '/preferences');
                if (this.form.ssn) this.form.ssn = '***-**-****';
                if (this.form.wasModified('onboard_status')) {
                    this.client.onboard_status = this.form.onboard_status;
                    this.lastStatusDate = moment.utc().format();
                }
            },

            sendConfirmation() {
                let component = this;
                let form = new Form();
                form.post('/business/clients/' + this.client.id + '/send_confirmation_email')
                    .then(function(response) {
                        component.lastStatusDate = moment.utc().format();
                    });
            }

        },

        computed: {
            business() {
                return this.client.business_id ? this.$store.getters.getBusiness(this.client.business_id) : {};
            },

            businessSendsSummaryEmails() {
                return !! this.business.shift_confirmation_email;
            },

            lastStatusUpdated() {
                return moment.utc(this.lastStatusDate).local().format('L') + ' at ' + moment.utc(this.lastStatusDate).local().format('LT');
            },

            onboardStatusText() {
                if (this.lastStatusDate) {
                    switch (this.form.onboard_status) {
                        case 'emailed_reconfirmation':
                            return 'The confirmation email was sent ' + this.lastStatusUpdated;
                        case 'agreement_signed': // paper signature
                            return 'Signed: ' + this.lastStatusUpdated;
                        case 'reconfirmed_checkbox': // electronic signature
                            return 'Signed Electronically: ' + this.lastStatusUpdated;
                    }
                    return 'The status was last updated ' + this.lastStatusUpdated;
                }
                return 'Select the Ally Agreement status of the client.';
            }
        }

    }
</script>

<style scoped>
    .pad-top {
        padding-top: 16px;
    }
</style>
