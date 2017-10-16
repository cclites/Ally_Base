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
                        <input-help :form="form" field="client_type" text=""></input-help>
                    </b-form-group>
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
                                <label class="col-form-label col-12 hidden-sm-down"><span>Confirmation Email</span></label>
                                <b-button  variant="info" @click="sendConfirmation()">Send Confirmation Email</b-button>
                            </b-form-group>
                            <b-form-group v-if="client.onboard_status=='emailed_reconfirmation'">
                                <label class="col-form-label col-12 hidden-sm-down"><span>Confirmation Email</span></label>
                                <b-button  variant="info" @click="sendConfirmation()">Resend Confirmation</b-button>
                            </b-form-group>
                        </b-col>
                    </b-row>
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
                    <b-form-group label="Date of Birth" label-for="date_of_birth">
                        <b-form-input
                                id="date_of_birth"
                                name="date_of_birth"
                                type="text"
                                v-model="form.date_of_birth"
                        >
                        </b-form-input>
                        <input-help :form="form" field="date_of_birth" text="Enter their date of birth. Ex: MM/DD/YYYY"></input-help>
                    </b-form-group>
                    <b-form-group label="Social Security Number" label-for="ssn">
                        <b-form-input
                                id="ssn"
                                name="ssn"
                                type="text"
                                v-model="form.ssn"
                        >
                        </b-form-input>
                        <input-help :form="form" field="ssn" text="Enter the client's social security number."></input-help>
                    </b-form-group>
                </b-col>
            </b-row>
            <b-row>
                <b-col lg="12">
                    <b-button id="save-profile" variant="success" type="submit">Save Profile</b-button>
                </b-col>
            </b-row>
        </form>
    </b-card>
</template>

<script>
    import ClientForm from '../mixins/ClientForm';

    export default {
        props: {
            'client': {},
            'lastStatusDate' : {},
        },

        data() {
            return {
                form: new Form({
                    firstname: this.client.user.firstname,
                    lastname: this.client.user.lastname,
                    email: this.client.user.email,
                    date_of_birth: moment(this.client.user.date_of_birth).format('L'),
                    client_type: this.client.client_type,
                    ssn: (this.client.hasSsn) ? '***-**-****' : '',
                    onboard_status: this.client.onboard_status,
                }),
            }
        },

        mounted() {
            if (!this.client) {
                form: new Form({
                    firstname: null,
                    lastname: null,
                    email: null,
                    date_of_birth: null,
                    ssn: null,
                    onboard_status: null,
                })
            }
        },

        methods: {

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
