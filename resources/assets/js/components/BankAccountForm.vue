<template>
    <form @submit.prevent="submit()" @keydown="form.clearError($event.target.name)">
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
                            v-on:cut.native.prevent
                            v-on:copy.native.prevent
                            v-on:paste.native.prevent
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
                            v-on:cut.native.prevent
                            v-on:copy.native.prevent
                            v-on:paste.native.prevent
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
                            v-on:cut.native.prevent
                            v-on:copy.native.prevent
                            v-on:paste.native.prevent
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
                            v-on:cut.native.prevent
                            v-on:copy.native.prevent
                            v-on:paste.native.prevent
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
        <b-form-group>
            <b-button variant="success" type="submit" size="">Save Bank Account</b-button>
        </b-form-group>
    </form>
</template>

<script>
    export default {
        props: {
            'submitUrl': '',
            'account': {},
            'source': {},
        },

        data() {
            return {
                'year': [],
                'months': [],
                'years': [],
                'form': new Form({
                    // todo update defaults for bank account info
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
            this.year = parseInt(moment().format('Y'));
            this.years = _.range(this.year, this.year+11);
            this.months = _.range(1,13).map(function(value) {
                return _.padStart(value, 2, '0');
            });
        },

        methods: {
            submit() {
                this.form.post(this.submitUrl)
                    .then((response) => {
                        this.form.account_number = '*****' + this.form.account_number.slice(-4);
                        this.form.account_number_confirmation = '';
                        this.form.routing_number = '*********';
                        this.form.routing_number_confirmation = '';
                        this.$parent.typeMessage = response.data;
                    });
            }
        }
    }
</script>