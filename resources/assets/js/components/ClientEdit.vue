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
                </b-col>
                <b-col lg="6">
                    <b-form-group label="Email Address" label-for="email">
                        <b-form-input
                            id="email"
                            name="email"
                            type="email"
                            v-model="form.email"
                            >
                        </b-form-input>
                        <input-help :form="form" field="email" text="Enter their email address.  Ex: user@domain.com"></input-help>
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
                    <b-form-group label="Date of Birth" label-for="date_of_birth">
                        <mask-input v-model="form.date_of_birth" id="date_of_birth" type="date"></mask-input>
                        <input-help :form="form" field="date_of_birth" text="Enter their date of birth. Ex: MM/DD/YYYY"></input-help>
                    </b-form-group>
                    <b-form-group label="Social Security Number" label-for="ssn">
                        <mask-input v-model="form.ssn" id="ssn" name="ssn" type="ssn"></mask-input>
                        <input-help :form="form" field="ssn" text="Enter the client's social security number."></input-help>
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
                            <b-form-group label="Ally Onboard Status" label-for="onboard_status">
                                <b-form-select
                                        id="onboard_status"
                                        name="onboard_status"
                                        v-model="form.onboard_status"
                                        :disabled="(form.onboard_status == 'reconfirmed_checkbox' || form.onboard_status == 'agreement_checkbox')"
                                >
                                    <option value="">--Select--</option>
                                    <option v-if="hiddenOnboardStatuses[form.onboard_status]" :value="form.onboard_status">{{ hiddenOnboardStatuses[form.onboard_status] }}</option>
                                    <option v-for="(display, value) in onboardStatuses" :value="value">{{ display }}</option>
                                </b-form-select>
                                <input-help :form="form" field="onboard_status" :text="onboardStatusText"></input-help>
                            </b-form-group>
                        </b-col>
                        <b-col xlg="4" lg="6" sm="12">
                            <b-form-group v-if="client.onboard_status=='needs_agreement'">
                                <label class="col-form-label col-12 hidden-sm-down"><span>Client Agreement Email</span></label>
                                <b-button  variant="info" @click="sendConfirmation()">Send Client Agreement via Email</b-button>
                            </b-form-group>
                            <b-form-group v-if="client.onboard_status=='emailed_reconfirmation'">
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
                    <b-button variant="danger" @click="deleteClient()"><i class="fa fa-times"></i> Delete Client</b-button>
                </b-col>
            </b-row>
        </form>

        <reset-password-modal v-model="passwordModal" :url="'/business/clients/' + this.client.id + '/password'"></reset-password-modal>
    </b-card>
</template>

<script>
    import ClientForm from '../mixins/ClientForm';

    export default {
        props: {
            'client': {},
            'lastStatusDate' : {},
            'confirmUrl': {},
        },

        data() {
            return {
                form: new Form({
                    firstname: this.client.firstname,
                    lastname: this.client.lastname,
                    email: this.client.email,
                    username: this.client.username,
                    date_of_birth: (this.client.date_of_birth) ? moment(this.client.date_of_birth).format('L') : null,
                    client_type: this.client.client_type,
                    ssn: (this.client.hasSsn) ? '***-**-****' : '',
                    onboard_status: this.client.onboard_status,
                }),
                passwordModal: false,
            }
        },

        mounted() {
            if (!this.client) {
                form: new Form({
                    firstname: null,
                    lastname: null,
                    email: null,
                    username: null,
                    date_of_birth: null,
                    ssn: null,
                    onboard_status: null,
                })
            }
        },

        methods: {

            deleteClient() {
                let form = new Form();
                if (confirm('Are you sure you wish to delete ' + this.client.name + '?')) {
                    form.submit('delete', '/business/clients/' + this.client.id);
                }
            },

            saveProfile() {
                let component = this;
                this.form.patch('/business/clients/' + this.client.id)
                    .then(function(response) {
                        if (component.form.ssn) component.form.ssn = '***-**-****';
                        if (component.form.wasModified('onboard_status')) {
                            component.client.onboard_status = component.form.onboard_status;
                            component.lastStatusDate = moment.utc().format();
                        }
                    })
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
            onboardStatusText() {
                if (this.lastStatusDate) {
                    if (this.form.onboard_status === 'emailed_reconfirmation') {
                        return 'The confirmation email was sent at ' + moment.utc(this.lastStatusDate).local().format('L LT');
                    }
                    return 'The status was last updated at ' + moment.utc(this.lastStatusDate).local().format('L LT');
                }
                return 'Select the Ally Agreement status of the client.';
            }
        },

        mixins: [ClientForm],

    }
</script>
