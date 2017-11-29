<template>
    <form @submit.prevent="submitForm()" @keydown="form.clearError($event.target.name)">

        <b-card header="Please confirm your information"
                header-bg-variant="info"
                header-text-variant="white"
        >
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
                        <input-help :form="form" field="firstname" text="Confirm your first name."></input-help>
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
                        <input-help :form="form" field="lastname" text="Confirm your last name."></input-help>
                    </b-form-group>
                    <b-form-group label="Email Address" label-for="email">
                        <b-form-input
                                id="email"
                                name="email"
                                type="email"
                                v-model="form.email"
                                required
                        >
                        </b-form-input>
                        <input-help :form="form" field="email" text="Confirm your email address.  Ex: user@domain.com"></input-help>
                    </b-form-group>
                    <b-form-group label="Username" label-for="username">
                        <b-form-input
                                id="email"
                                name="email"
                                type="text"
                                :value="caregiver.username"
                                disabled
                        >
                        </b-form-input>
                        <input-help :form="form" field="email" text="View your username which will be used to logging in."></input-help>
                    </b-form-group>
                    <b-form-group label="Password" label-for="password">
                        <b-form-input
                                id="password"
                                name="password"
                                type="password"
                                v-model="form.password"
                                required
                        >
                        </b-form-input>
                        <input-help :form="form" field="password" text="Enter a new password that will be used for logging in."></input-help>
                    </b-form-group>
                    <b-form-group label="Confirm Password" label-for="password_confirmation">
                        <b-form-input
                                id="password_confirmation"
                                name="password_confirmation"
                                type="password"
                                v-model="form.password_confirmation"
                                required
                        >
                        </b-form-input>
                        <input-help :form="form" field="password_confirmation" text="Re-enter the above password for confirmation."></input-help>
                    </b-form-group>
                </b-col>
                <b-col lg="6">
                    <b-form-group label="Date of Birth" label-for="date_of_birth">
                        <b-form-input
                                id="date_of_birth"
                                name="date_of_birth"
                                type="text"
                                v-model="form.date_of_birth"
                        >
                        </b-form-input>
                        <input-help :form="form" field="date_of_birth" text="Confirm your date of birth. Ex: MM/DD/YYYY"></input-help>
                    </b-form-group>
                    <b-form-group label="Phone Number" label-for="phone_number">
                        <b-form-input
                                id="phone_number"
                                name="phone_number"
                                type="text"
                                v-model="form.phone_number"
                        >
                        </b-form-input>
                        <input-help :form="form" field="phone_number" text="Confirm your full phone number."></input-help>
                    </b-form-group>
                    <b-form-group label="Address">
                        <b-row>
                            <b-col sm="12">
                                <b-form-input
                                        id="address1"
                                        name="address1"
                                        type="text"
                                        v-model="form.address1"
                                >
                                </b-form-input>
                                <input-help :form="form" field="address1" text="Confirm your street address."></input-help>
                            </b-col>
                        </b-row>
                        <b-row>
                            <b-col sm="12">
                                <b-form-input
                                        id="address2"
                                        name="address2"
                                        type="text"
                                        v-model="form.address2"
                                >
                                </b-form-input>
                                <input-help :form="form" field="address2" text="Confirm your address second line (apartment number or other)."></input-help>
                            </b-col>
                        </b-row>
                        <b-row>
                            <b-col sm="5" xs="6">
                                <b-form-input
                                        id="city"
                                        name="city"
                                        type="text"
                                        v-model="form.city"
                                >
                                </b-form-input>
                                <input-help :form="form" field="city" text="Confirm your city."></input-help>
                            </b-col>
                            <b-col sm="3" xs="2">
                                <b-form-input
                                        id="state"
                                        name="state"
                                        type="text"
                                        v-model="form.state"
                                >
                                </b-form-input>
                                <input-help :form="form" field="state" text="Confirm your state."></input-help>
                            </b-col>
                            <b-col sm="4" xs="4">
                                <b-form-input
                                        id="zip"
                                        name="zip"
                                        type="text"
                                        v-model="form.zip"
                                >
                                </b-form-input>
                                <input-help :form="form" field="zip" text="Confirm your zip."></input-help>
                            </b-col>
                        </b-row>
                    </b-form-group>
                </b-col>
            </b-row>
        </b-card>
        <b-row>
            <b-col cols="12" md="6" lg="8">
                <b-card
                        header="Direct Deposit Bank Account"
                        header-text-variant="white"
                        header-bg-variant="info"
                >
                    <p class="card-text">I hereby authorize Ally, LLC (Ally) to debit entries, and if necessary credit entries to correct erroneous debits from the account at the Financial Institution named above.  If a debit is scheduled to take place on a non-banking date, the transaction will take place on the next banking day. I (we) acknowledge the origination of ACH transactions to our account must comply with the provisions of U.S. law.  This authority is to remain in full force and effect until Ally has received written notification from me of termination in such time and manner as to afford Ally and Financial Institution a reasonable opportunity to act on it.
                    </p>
                    <b-form-group label="Nickname" label-for="nickname">
                        <b-form-input
                                id="nickname"
                                name="nickname"
                                type="text"
                                v-model="form.nickname"
                        >
                        </b-form-input>
                        <input-help :form="form" field="nickname" text="Optionally provide a nickname for this account."></input-help>
                    </b-form-group>
                    <b-form-group label="Name on Account" label-for="name_on_account">
                        <b-form-input
                                id="name_on_account"
                                name="name_on_account"
                                type="text"
                                v-model="form.name_on_account"
                        >
                        </b-form-input>
                        <input-help :form="form" field="name_on_account" text="Please enter your name, as it appears on the account."></input-help>
                    </b-form-group>
                    <b-row>
                        <b-col lg="6">
                            <b-form-group label="Routing Number" label-for="routing_number">
                                <b-form-input
                                        id="routing_number"
                                        name="routing_number"
                                        type="text"
                                        autocomplete="off"
                                        v-model="form.routing_number"
                                >
                                </b-form-input>
                                <input-help :form="form" field="routing_number" text="Provide your bank's routing number"></input-help>
                            </b-form-group>
                        </b-col>
                        <b-col lg="6">
                            <b-form-group label="Confirm Routing Number" label-for="routing_number_confirmation">
                                <b-form-input
                                        id="routing_number_confirmation"
                                        name="routing_number_confirmation"
                                        type="text"
                                        autocomplete="off"
                                        v-model="form.routing_number_confirmation"
                                >
                                </b-form-input>
                                <input-help :form="form" field="routing_number_confirmation" text="Re-enter your bank's routing number"></input-help>
                            </b-form-group>
                        </b-col>
                    </b-row>
                    <b-row>
                        <b-col lg="6">
                            <b-form-group label="Account Number" label-for="account_number">
                                <b-form-input
                                        id="account_number"
                                        name="account_number"
                                        type="text"
                                        autocomplete="off"
                                        v-model="form.account_number"
                                >
                                </b-form-input>
                                <input-help :form="form" field="account_number" text="Provide your bank's account number"></input-help>
                            </b-form-group>
                        </b-col>
                        <b-col lg="6">
                            <b-form-group label="Confirm Account Number" label-for="account_number_confirmation">
                                <b-form-input
                                        id="account_number_confirmation"
                                        name="account_number_confirmation"
                                        type="text"
                                        autocomplete="off"
                                        v-model="form.account_number_confirmation"
                                >
                                </b-form-input>
                                <input-help :form="form" field="account_number_confirmation" text="Re-enter your bank's account number"></input-help>
                            </b-form-group>
                        </b-col>
                    </b-row>
                    <b-row>
                        <b-col lg="6">
                            <b-form-group label="Account Type" label-for="account_type">
                                <b-form-select
                                        id="account_type"
                                        name="account_type"
                                        v-model="form.account_type"
                                >
                                    <option value="checking">Checking</option>
                                    <option value="savings">Savings</option>
                                </b-form-select>
                                <input-help :form="form" field="account_type" text="Select the bank account type"></input-help>
                            </b-form-group>
                        </b-col>
                        <b-col lg="6">
                            <b-form-group label="Holder Type" label-for="account_holder_type">
                                <b-form-select
                                        id="account_holder_type"
                                        name="account_holder_type"
                                        v-model="form.account_holder_type"
                                >
                                    <option value="personal">Personal</option>
                                    <option value="business">Business</option>
                                </b-form-select>
                                <input-help :form="form" field="account_holder_type" text="Select the holder type"></input-help>
                            </b-form-group>
                        </b-col>
                    </b-row>
                </b-card>
            </b-col>
            <b-col cols="12" md="6" lg="4">
                <b-card
                    header="Example Check"
                    header-text-variant="white"
                    header-bg-variant="info"
                    img-src="https://www.howardbank.com/wp-content/uploads/how-to-void-a-check.gif"
                    img-alt="Voided Check"
                    img-bottom
                    >
                </b-card>
            </b-col>
        </b-row>
        
        <b-row>
            <b-col lg="12" class="text-right">
                <div class="form-check">
                    <label class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" name="accepted_terms" v-model="form.accepted_terms" value="1">
                        <span class="custom-control-indicator"></span>
                        <span class="custom-control-description"><b>I accept the direct deposit terms above</b></span>
                    </label>
                    <input-help :form="form" field="accepted_terms" text=""></input-help>
                </div>
                <b-button variant="success" size="lg" type="submit">Accept and Verify</b-button>
            </b-col>
        </b-row>
    </form>
</template>

<script>
    export default {
        props: {
            'token': {},
            'caregiver': {},
            'phoneNumber': {},
            'address': {},
            'termsUrl': String,
            'account': {
                default() {
                    return {};
                }
            }
        },

        data() {
            return {
                form: new Form({
                    firstname: this.caregiver.firstname,
                    lastname: this.caregiver.lastname,
                    email: this.caregiver.email,
                    date_of_birth: moment(this.caregiver.user.date_of_birth).format('L'),
                    phone_number: this.phoneNumber,
                    address1: this.address.address1,
                    address2: this.address.address2,
                    city: this.address.city,
                    state: this.address.state,
                    zip: this.address.zip,
                    country: 'US', // This form assumes US addresses ONLY!
                    accepted_terms: 0,
                    password: null,
                    password_confirmation: null,
                    // Bank Account
                    nickname: this.account.nickname,
                    name_on_account: this.account.name_on_account,
                    routing_number: (this.account.last_four) ? '*********' : '',
                    routing_number_confirmation: '',
                    account_number: (this.account.last_four) ? '*****' + this.account.last_four : '',
                    account_number_confirmation: '',
                    account_type: this.account.account_type,
                    account_holder_type: this.account.account_holder_type,
                }),

            }
        },

        mounted() {

        },

        methods: {

            submitForm() {
                this.form.post('/confirm/caregiver/' + this.token)
                    .then(response => {
                        window.location = '/confirm/saved';
                    });
            }

        }


    }
</script>
