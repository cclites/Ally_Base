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
                    <b-form-group label="Title" label-for="title" label-class="required">
                        <b-form-input
                                id="title"
                                name="title"
                                type="text"
                                v-model="form.title"
                        >
                        </b-form-input>
                        <input-help :form="form" field="title" text="Enter the caregiver's title (example: CNA)"></input-help>
                    </b-form-group>
                    <b-form-group label="Certification" label-for="certification" label-class="required">
                        <b-form-select
                                id="certification"
                                name="certification"
                                v-model="form.certification"
                        >
                            <option value="">None</option>
                            <option value="CNA">CNA</option>
                            <option value="HHA">HHA</option>
                            <option value="RN">RN</option>
                            <option value="LPN">LPN</option>
                        </b-form-select>
                        <input-help :form="form" field="certification" text="Select the caregiver's certification / license."></input-help>
                    </b-form-group>
                    <b-form-group label="Caregiver Status">
                        <b-form-select :options="statusAliasOptions" name="status_alias_id" v-model="form.status_alias_id">
                            <option value="">{{ active ? 'Active' : 'Inactive' }}</option>
                        </b-form-select>
                        <input-help :form="form" field="status_alias_id"></input-help>
                    </b-form-group>
                    <b-form-group label="Social Security Number" label-for="ssn">
                        <mask-input v-model="form.ssn" id="ssn" name="ssn" type="ssn"></mask-input>
                    </b-form-group>
                    <b-form-group label="Gender">
                        <b-form-radio-group id="gender" v-model="form.gender">
                            <b-form-radio value="M">Male</b-form-radio>
                            <b-form-radio value="F">Female</b-form-radio>
                        </b-form-radio-group>
                    </b-form-group>
                    <b-form-group label="Medicaid ID" label-for="medicaid_id">
                        <b-form-input
                                id="medicaid_id"
                                type="text"
                                v-model="form.medicaid_id"
                        >
                        </b-form-input>
                        <input-help :form="form" field="medicaid_id" text="The caregiver ID, or license number, for Medicaid"></input-help>
                    </b-form-group>
                    <b-form-group label="Orientation Date">
                        <date-picker id="orientation_date" v-model="form.orientation_date"></date-picker>
                    </b-form-group>
                    <b-form-group label="Application Date">
                        <date-picker id="application_date" v-model="form.application_date"></date-picker>
                    </b-form-group>
                    <b-form-group>
                        <business-referral-source-select v-model="form.referral_source_id" source-type="caregiver"></business-referral-source-select>
                        <input-help :form="form" field="referred_by" text="Enter how the caregiver was referred." />
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
                                        <input type="checkbox" class="custom-control-input" name="no_email" v-model="form.no_email" value="1">
                                        <span class="custom-control-indicator"></span>
                                        <span class="custom-control-description">No Email</span>
                                    </label>
                                </div>
                            </b-col>
                        </b-row>
                        <input-help :form="form" field="email" text="Enter their email address or check the box if caregiver does not have an email."></input-help>
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
                                        <span class="custom-control-description">Let Caregiver Choose</span>
                                    </label>
                                </div>
                            </b-col>
                        </b-row>
                        <input-help :form="form" field="username" text="Enter their username to be used for logins."></input-help>
                    </b-form-group>
                    <b-form-group label="Date of Birth" label-for="date_of_birth">
                        <mask-input v-model="form.date_of_birth" id="date_of_birth" type="date"></mask-input>
                        <input-help :form="form" field="date_of_birth" text="Enter their date of birth. Ex: MM/DD/YYYY"></input-help>
                    </b-form-group>
                    <b-form-group label="Photo">
                        <edit-avatar v-model="form.avatar" :size="150" :cropperPadding="100" />
                    </b-form-group>
                    <b-form-group label="Confirmed Service Hours">
                        <div class="mb-2"><strong>Lifetime: </strong>{{ caregiver.hours_total.toLocaleString() }}</div>
                        <div class="mb-2"><strong>Last 90 Days: </strong>{{ caregiver.hours_last_90.toLocaleString() }}</div>
                        <div class="mb-2"><strong>Last 30 Days: </strong>{{ caregiver.hours_last_30.toLocaleString() }}</div>
                    </b-form-group>
                    <b-form-group label="Is the caregiver okay with smoking?" label-for="smoking_okay">
                        <b-form-select id="smoking_okay" v-model="form.smoking_okay">
                            <option :value="1">Yes</option>
                            <option :value="0">No</option>
                        </b-form-select>
                        <input-help :form="form" field="smoking_okay" text="" />
                    </b-form-group>
                    <b-form-group label="Acceptable Pets:">
                        <b-form-checkbox v-model="form.pets_dogs_okay" value="1" unchecked-value="0">Dogs</b-form-checkbox>
                        <b-form-checkbox v-model="form.pets_cats_okay" value="1" unchecked-value="0">Cats</b-form-checkbox>
                        <b-form-checkbox v-model="form.pets_birds_okay" value="1" unchecked-value="0">Birds</b-form-checkbox>
                    </b-form-group>
                </b-col>
            </b-row>
            <b-row>
                <b-col lg="12">
                    <b-button variant="success" type="submit">Save Profile</b-button>
                    <b-button variant="primary" @click="passwordModal = true"><i class="fa fa-lock"></i> Reset Password</b-button>
                    <b-button variant="info" @click="welcomeEmailModal = true"><i class="fa fa-mail-forward"></i> Send Welcome Email</b-button>
                    <b-button variant="danger" @click="$refs.deactivateCaregiverModal.show()" v-if="active"><i class="fa fa-times"></i> Deactivate Caregiver</b-button>
                    <b-button variant="info" @click="activateModal = true" v-else><i class="fa fa-refresh"></i> Re-activate Caregiver</b-button>
                </b-col>
            </b-row>
        </form>

        <reset-password-modal v-model="passwordModal" :url="'/business/caregivers/' + this.caregiver.id + '/password'"></reset-password-modal>
        <send-welcome-email-modal v-model="welcomeEmailModal" :user='caregiver' :url="'/business/caregivers/' + this.caregiver.id + '/send_confirmation_email'"></send-welcome-email-modal>

        <deactivate-caregiver-modal :caregiver="caregiver" ref="deactivateCaregiverModal"></deactivate-caregiver-modal>

        <b-modal id="activateModal"
            title="Are you sure?"
            @ok="reactivateCaregiver"
            v-model="activateModal">
                Are you sure you wish to re-activate {{ this.caregiver.name }}?
        </b-modal>
    </b-card>
</template>

<script>
    import FormatsDates from '../mixins/FormatsDates';
    import DeactivateCaregiverModal from './modals/DeactivateCaregiverModal';
    import { mapGetters } from 'vuex'

    export default {
        props: {
            'caregiver': {},
        },

        mixins: [FormatsDates],
        
        components: {
          DeactivateCaregiverModal
        },

        data() {
            return {
                form: new Form({
                    firstname: this.caregiver.firstname,
                    lastname: this.caregiver.lastname,
                    email: this.caregiver.email,
                    username: this.caregiver.username,
                    no_username: false,
                    title: this.caregiver.title,
                    certification: this.caregiver.certification ? this.caregiver.certification : '',
                    date_of_birth: (this.caregiver.user.date_of_birth) ? moment(this.caregiver.user.date_of_birth).format('L') : null,
                    no_email: false,
                    ssn: this.caregiver.masked_ssn,
                    gender: this.caregiver.gender,
                    medicaid_id: this.caregiver.medicaid_id,
                    avatar: this.caregiver.avatar,
                    orientation_date: this.caregiver.orientation_date ? this.formatDate(this.caregiver.orientation_date) : '',
                    application_date: this.caregiver.application_date ? this.formatDate(this.caregiver.application_date) : '',
                    referral_source_id: this.caregiver.referral_source_id ? this.caregiver.referral_source_id : "",
                    status_alias_id: this.caregiver.status_alias_id || '',
                    smoking_okay: this.caregiver.smoking_okay,
                    pets_dogs_okay: this.caregiver.pets_dogs_okay,
                    pets_cats_okay: this.caregiver.pets_cats_okay,
                    pets_birds_okay: this.caregiver.pets_birds_okay,
                }),
                passwordModal: false,
                welcomeEmailModal: false,
                active: this.caregiver.active,
                activateModal: false,
                inactive_at: '',
                statusAliases: [],
            }
        },

        mounted() {
            this.checkForNoEmailDomain();
            this.checkForNoUsername();
            this.fetchStatusAliases();
        },

        computed: {
            statusAliasOptions() {
                if (! this.statusAliases || !this.statusAliases.caregiver) {
                    return [];
                }

                return this.statusAliases.caregiver.filter(item => {
                    return item.active == this.active;
                }).map(item => {
                    return {
                        value: item.id,
                        text: item.name,
                    };
                });
            },
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

            checkForNoUsername() {
                if (this.form.username) {
                    if (this.form.username.substr(0, 9) == 'no_login_') {
                        this.form.no_username = true;
                        this.form.username = null;
                    }
                }
            },

            reactivateCaregiver() {
                let form = new Form();
                form.post('/business/caregivers/' + this.caregiver.id + '/reactivate')
                    .then(response => this.active = 1);
            },

            saveProfile() {
                this.form.patch('/business/caregivers/' + this.caregiver.id)
                    .then(({ data }) => {
                        this.form.avatar = data.data.avatar;
                    })
            },

            fetchStatusAliases() {
                axios.get('/business/status-aliases')
                    .then( ({ data }) => {
                        if (data && data.caregiver) {
                            this.statusAliases = data;
                        } else {
                            this.statusAliases = {caregiver: [], client: []};
                        }
                    })
                    .catch(e => {
                    })
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
        }
    }
</script>
