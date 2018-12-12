<template>
    <form @submit.prevent="submit()" @keydown="formKeyDown($event)">
        <b-form-group label="Nickname" label-for="nickname">
            <b-form-input type="text" v-model="form.nickname" :readonly="readonly" />
            <input-help :form="form" field="nickname" text="Optionally provide a nickname for this account."></input-help>
        </b-form-group>
        <b-form-group label="Name on Account" label-for="name_on_account">
            <b-form-input type="text" v-model="form.name_on_account" :readonly="readonly" />
            <input-help :form="form" field="name_on_account" text="Please enter your name, as it appears on the account."></input-help>
        </b-form-group>
        <b-row>
            <b-col lg="6">
                <b-form-group label="Routing Number" label-for="routing_number">
                    <b-form-input type="text"
                            autocomplete="off"
                            v-model="form.routing_number"
                            v-on:cut.native.prevent
                            v-on:copy.native.prevent
                            v-on:paste.native.prevent
                            :readonly="readonly"
                    />
                    <input-help :form="form" field="routing_number" text="Provide your bank's routing number"></input-help>
                </b-form-group>
            </b-col>
            <b-col lg="6">
                <b-form-group label="Confirm Routing Number" label-for="routing_number_confirmation">
                    <b-form-input type="text"
                            autocomplete="off"
                            v-model="form.routing_number_confirmation"
                            v-on:cut.native.prevent
                            v-on:copy.native.prevent
                            v-on:paste.native.prevent
                            :readonly="readonly"
                    />
                    <input-help :form="form" field="routing_number_confirmation" text="Re-enter your bank's routing number"></input-help>
                </b-form-group>
            </b-col>
        </b-row>
        <b-row>
            <b-col lg="6">
                <b-form-group label="Account Number" label-for="account_number">
                    <b-form-input type="text"
                            autocomplete="off"
                            v-model="form.account_number"
                            v-on:cut.native.prevent
                            v-on:copy.native.prevent
                            v-on:paste.native.prevent
                            :readonly="readonly"
                    />
                    <input-help :form="form" field="account_number" text="Provide your bank's account number"></input-help>
                </b-form-group>
            </b-col>
            <b-col lg="6">
                <b-form-group label="Confirm Account Number" label-for="account_number_confirmation">
                    <b-form-input type="text"
                            autocomplete="off"
                            v-model="form.account_number_confirmation"
                            v-on:cut.native.prevent
                            v-on:copy.native.prevent
                            v-on:paste.native.prevent
                            :readonly="readonly"
                    />
                    <input-help :form="form" field="account_number_confirmation" text="Re-enter your bank's account number"></input-help>
                </b-form-group>
            </b-col>
        </b-row>
        <b-row>
            <b-col lg="6">
                <b-form-group label="Account Type" label-for="account_type">
                    <b-form-select v-model="form.account_type" :disabled="readonly">
                        <option value="checking">Checking</option>
                        <option value="savings">Savings</option>
                    </b-form-select>
                    <input-help :form="form" field="account_type" text="Select the bank account type"></input-help>
                </b-form-group>
            </b-col>
            <b-col lg="6">
                <b-form-group label="Holder Type" label-for="account_holder_type">
                    <b-form-select v-model="form.account_holder_type" :disabled="readonly">
                        <option value="personal">Personal</option>
                        <option value="business">Business</option>
                    </b-form-select>
                    <input-help :form="form" field="account_holder_type" text="Select the holder type"></input-help>
                </b-form-group>
            </b-col>
        </b-row>
        <b-form-group>
            <b-button :variant="buttonVariant" type="submit" size="" :disabled="readonly || submitting">
                <i class="fa fa-spin fa-spinner" v-show="submitting"></i> {{ buttonText }}
            </b-button>
        </b-form-group>
    </form>
</template>

<script>
    export default {
        props: {
            'submitUrl': '',
            'account': {},
            'source': {},
            'businessId': '',
            'readonly': false,
        },

        data() {
            return {
                'year': [],
                'months': [],
                'years': [],
                'form': this.makeForm(this.account),
                submitting: false,
                buttonText: '',
                buttonVariant: '',
            }
        },

        mounted() {
            this.resetButtonText();
            this.year = parseInt(moment().format('Y'));
            this.years = _.range(this.year, this.year+11);
            this.months = _.range(1,13).map(function(value) {
                return _.padStart(value, 2, '0');
            });
        },

        methods: {
            async submit() {
                this.buttonText = 'Verifying Account...';
                this.submitting = true;
                try {
                    const response = await this.form.post(this.submitUrl);
                    this.form.account_number = '*****' + this.form.account_number.slice(-4);
                    this.form.account_number_confirmation = '';
                    this.form.routing_number = '*********';
                    this.form.routing_number_confirmation = '';
                    this.$parent.typeMessage = response.data;
                }
                catch (e) {
                    let errors = e.response.data.errors;
                    if (errors && Object.keys(errors)[0] === 'account_number') {
                        this.form.ignore_validation = true;
                        alert('Please double check your routing number and account number.  If you are sure your numbers are correct, press Save again.');
                    }
                }
                this.resetButtonText();
                this.submitting = false;
            },

            makeForm(account) {
                let defaults = {};
                if (this.businessId) defaults.business_id = this.businessId;

                return new Form({
                    ...defaults,
                    nickname: account.nickname,
                    name_on_account: account.name_on_account,
                    routing_number: (account.last_four) ? '*********' : '',
                    routing_number_confirmation: '',
                    account_number: (account.last_four) ? '*****' + account.last_four : '',
                    account_number_confirmation: '',
                    account_type: account.account_type,
                    account_holder_type: account.account_holder_type,
                    ignore_validation: false,
                })
            },

            resetButtonText() {
                this.buttonText = 'Save Bank Account';
                this.buttonVariant = 'success';
            },

            formKeyDown(event) {
                this.resetButtonText();
                this.form.clearError(event.target.name);
            }
        },

        watch: {
            account(val) {
                this.form = this.makeForm(val);
            }
        }
    }
</script>
