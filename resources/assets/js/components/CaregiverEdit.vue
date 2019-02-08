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
                    <b-form-group label="Date of Birth" label-for="date_of_birth">
                        <mask-input v-model="form.date_of_birth" id="date_of_birth" type="date"></mask-input>
                        <input-help :form="form" field="date_of_birth" text="Enter their date of birth. Ex: MM/DD/YYYY"></input-help>
                    </b-form-group>
                    <b-form-group label="Photo">
                        <edit-avatar v-model="form.avatar" :size="150" :cropperPadding="100" />
                    </b-form-group>
                </b-col>
            </b-row>
            <b-row>
                <b-col lg="12">
                    <b-button variant="success" type="submit">Save Profile</b-button>
                    <b-button variant="primary" @click="passwordModal = true"><i class="fa fa-lock"></i> Reset Password</b-button>
                    <b-button variant="info" @click="welcomeEmailModal = true"><i class="fa fa-mail-forward"></i> Send Welcome Email</b-button>
                    <b-button variant="danger" @click="deactivateModal = true" v-if="active"><i class="fa fa-times"></i> Deactivate Caregiver</b-button>
                    <b-button variant="info" @click="activateModal = true" v-else><i class="fa fa-refresh"></i> Re-activate Caregiver</b-button>
                </b-col>
            </b-row>
        </form>

        <reset-password-modal v-model="passwordModal" :url="'/business/caregivers/' + this.caregiver.id + '/password'"></reset-password-modal>
        <send-welcome-email-modal v-model="welcomeEmailModal" :user='caregiver' :url="'/business/caregivers/' + this.caregiver.id + '/send_confirmation_email'"></send-welcome-email-modal>

        <b-modal id="deactivateModal"
                 title="Are you sure?"
                 v-model="deactivateModal"
                 ok-title="OK">
            <b-container fluid>
                <b-row>
                    <b-col lg="12" class="text-center">
                        <div class="mb-3">Are you sure you wish to archive {{ this.caregiver.name }}?</div>
                        <div v-if="caregiver.future_schedules > 0">All <span class="text-danger">{{ this.caregiver.future_schedules }}</span> of their future scheduled shifts will be unassigned.</div>
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
                <b-btn v-if="caregiver.future_schedules > 0" variant="danger" class="mr-2" @click.prevent="archiveCaregiver">Yes - Unassign Future Schedules</b-btn>
                <b-btn v-else variant="danger" class="mr-2" @click.prevent="archiveCaregiver">Yes</b-btn>
               <b-btn variant="default" @click="deactivateModal = false">Cancel</b-btn>
            </div>
        </b-modal>

        <b-modal id="activateModal"
            title="Are you sure?"
            @ok="reactivateCaregiver"
            v-model="activateModal">
                Are you sure you wish to re-activate {{ this.caregiver.name }}?
        </b-modal>
    </b-card>
</template>

<script>
    export default {
        props: {
            'caregiver': {},
        },

        data() {
            return {
                form: new Form({
                    firstname: this.caregiver.firstname,
                    lastname: this.caregiver.lastname,
                    email: this.caregiver.email,
                    username: this.caregiver.username,
                    title: this.caregiver.title,
                    date_of_birth: (this.caregiver.user.date_of_birth) ? moment(this.caregiver.user.date_of_birth).format('L') : null,
                    no_email: false,
                    ssn: this.caregiver.masked_ssn,
                    gender: this.caregiver.gender,
                    medicaid_id: this.caregiver.medicaid_id,
                    avatar: this.caregiver.avatar,
                    referral_source_id: this.caregiver.referral_source_id ? this.caregiver.referral_source_id : "",
                }),
                passwordModal: false,
                welcomeEmailModal: false,
                active: this.caregiver.active,
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

            archiveCaregiver() {
                let form = new Form();
                form.submit('delete', `/business/caregivers/${this.caregiver.id}?inactive_at=${this.inactive_at}`);
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
            }
        }
    }
</script>
