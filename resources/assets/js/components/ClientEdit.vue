<template>
    <b-card header="Profile"
        header-bg-variant="info"
        header-text-variant="white"
        >
        <form @submit.prevent="saveProfile()" @keydown="form.clearError($event.target.name)">
            <b-row>
                <b-col lg="6">
                    <b-form-group label="First Name" label-for="firstname">
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
                    <b-form-group label="Last Name" label-for="lastname">
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
                    <b-form-group label="Client Type" label-for="client_type">
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
                </b-col>
                <b-col lg="6">
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
                    <b-form-group label="Username" label-for="username">
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
                </b-col>
            </b-row>
            <b-row>
                <b-col lg="6">
                    <b-form-group label="Date inquired about Service">
                        <date-picker id="inquiry_date" v-model="form.inquiry_date"></date-picker>
                    </b-form-group>
                    <b-form-group label="How were they referred?">
                        <b-form-input id="referral" v-model="form.referral"></b-form-input>
                    </b-form-group>
                    <b-form-group>
                        <b-form-checkbox id="ambulatory"
                                         v-model="form.ambulatory"
                                         :value="true"
                                         :unchecked-value="false">
                            Ambulatory
                        </b-form-checkbox>
                    </b-form-group>
                </b-col>
                <b-col lg="6">
                    <b-form-group label="Service Start Date">
                        <date-picker id="service_start_date" v-model="form.service_start_date"></date-picker>
                    </b-form-group>
                    <b-form-group label="Diagnosis">
                        <b-form-input id="diagnosis" v-model="form.diagnosis"></b-form-input>
                    </b-form-group>
                </b-col>
            </b-row>

            <b-row>
                <b-col>
                    <p class="h6">Power of Attorney</p>
                    <hr>
                </b-col>
            </b-row>
            <b-row>
                <b-col lg="6">
                    <b-form-group label="First Name">
                        <b-form-input id="poa_first_name"
                                      v-model="form.poa_first_name"></b-form-input>
                    </b-form-group>
                    <b-form-group label="Phone">
                        <b-form-input id="poa_phone"
                                      v-model="form.poa_phone"></b-form-input>
                    </b-form-group>
                </b-col>
                <b-col lg="6">
                    <b-form-group label="Last Name">
                        <b-form-input id="poa_last_name"
                                      v-model="form.poa_last_name"></b-form-input>
                    </b-form-group>
                    <b-form-group label="Relationship">
                        <b-form-input id="poa_relationship"
                                      v-model="form.poa_relationship"></b-form-input>
                    </b-form-group>
                </b-col>
            </b-row>
            <b-row>
                <b-col>
                    <p class="h6">Physician</p>
                    <hr>
                </b-col>
            </b-row>
            <b-row>
                <b-col lg="6">
                    <b-form-group label="First Name">
                        <b-form-input id="dr_first_name"
                                      v-model="form.dr_first_name"></b-form-input>
                    </b-form-group>
                    <b-form-group label="Phone">
                        <b-form-input id="dr_phone"
                                      v-model="form.dr_phone"></b-form-input>
                    </b-form-group>
                </b-col>
                <b-col lg="6">
                    <b-form-group label="Last Name">
                        <b-form-input id="dr_last_name"
                                      v-model="form.dr_last_name"></b-form-input>
                    </b-form-group>
                    <b-form-group label="Fax">
                        <b-form-input id="dr_fax"
                                      v-model="form.dr_fax"></b-form-input>
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
                <b-col lg="6">
                    <b-row>
                        <b-col xlg="8" lg="6" sm="12">
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
                        <b-col xlg="4" lg="6" sm="12">
                            <b-form-group v-if="client.onboard_status == 'needs_agreement'">
                                <label class="col-form-label col-12 hidden-sm-down"><span>Client Agreement Email</span></label>
                                <b-button  variant="info" @click="sendConfirmation()">Send Client Agreement via Email</b-button>
                            </b-form-group>
                            <b-form-group v-if="client.onboard_status == 'emailed_reconfirmation'">
                                <label class="col-form-label col-12 hidden-sm-down"><span>Client Agreement Email</span></label>
                                <b-button  variant="info" @click="sendConfirmation()">Resend Client Agreement via Email</b-button>
                            </b-form-group>
                        </b-col>
                    </b-row>
                </b-col>
                <b-col lg="6">
                    <b-form-group label="Confirmation URL" label-for="ssn" v-if="confirmUrl && (form.onboard_status=='needs_agreement' || form.onboard_status=='emailed_reconfirmation')">
                        <a :href="confirmUrl" target="_blank">{{ confirmUrl }}</a>
                        <input-help :form="form" field="confirmUrl" text="The URL the client can use to confirm their Ally agreement."></input-help>
                    </b-form-group>
                </b-col>
            </b-row>
            <b-row>
                <b-col lg="12">
                    <b-button id="save-profile" variant="success" type="submit">Save Profile</b-button>
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
    </b-card>
</template>

<script>
    import ClientForm from '../mixins/ClientForm';
    import DatePicker from './DatePicker';
    import FormatsDates from '../mixins/FormatsDates';
    window.croppie = require('croppie');

    export default {
        props: {
            'client': {},
            'lastStatusDate' : {},
            'confirmUrl': {},
        },

        mixins: [ClientForm, FormatsDates],

        components: {
            DatePicker
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
                    referral: this.client.referral,
                    diagnosis: this.client.diagnosis,
                    ambulatory: !!this.client.ambulatory,
                    gender: this.client.gender,
                    poa_first_name: this.client.poa_first_name,
                    poa_last_name: this.client.poa_last_name,
                    poa_phone: this.client.poa_phone,
                    poa_relationship: this.client.poa_relationship,
                    dr_first_name: this.client.dr_first_name,
                    dr_last_name: this.client.dr_last_name,
                    dr_phone: this.client.dr_phone,
                    dr_fax: this.client.dr_fax,
                    hospital_name: this.client.hospital_name,
                    hospital_number: this.client.hospital_number,
                    avatar: this.client.avatar,
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
            }
        },

        mounted() {
            this.checkForNoEmailDomain();
        },

        methods: {
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
