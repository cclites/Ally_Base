<template>
    <div>
        <loading-card v-if="loading" text="Loading account information..."></loading-card>

        <form v-else @submit.prevent="submit()" @keydown="form.clearError($event.target.name)" autocomplete="off">
            <b-row>
                <b-col lg="12">
                    <b-card header="Enter Payment Details"
                            header-bg-variant="info"
                            header-text-variant="white"
                    >
                        <b-row>
                            <b-col xs="12" lg="6">
                                <b-form-group label="Payment Method" label-for="type">
                                    <b-form-select
                                        id="type"
                                        name="type"
                                        v-model="type"
                                    >
                                        <option value="">--- Select Payment Method ---</option>
                                        <option value="credit_card">Credit Card</option>
                                        <option value="bank_account">Bank Account</option>
                                    </b-form-select>
                                </b-form-group>
                                <credit-card-form v-if="type == 'credit_card'" 
                                    source="primary"
                                    :card="{}"
                                    :client="client"
                                    :submit-url="null"
                                    :readonly="false"
                                    ref="creditCardForm"
                                />
                                <bank-account-form v-if="type == 'bank_account'" 
                                    source="primary"
                                    :account="{}"
                                    :submit-url="null"
                                    :readonly="false"
                                    ref="bankAccountForm"
                                />
                            </b-col>
                            <b-col xs="12" lg="6" v-if="type == 'bank_account'">
                                <div><label for="example-check">Example Check</label></div>
                                <img src="https://www.howardbank.com/wp-content/uploads/how-to-void-a-check.gif" alt="Voided Check" />
                            </b-col>
                        </b-row>
                    </b-card>
                    <div class="text-right">
                        <b-button variant="success" size="lg" type="submit" :disabled="busy">
                            <i v-if="busy" class="fa fa-spinner fa-spin mr-2" size="lg"></i>
                            Save and Finish Account Setup
                        </b-button>
                    </div>
                </b-col>
            </b-row>
        </form>
    </div>
</template>

<script>
    export default {
        props: {
            'token': {},
            'client': {},
        },

        data() {
            return {
                busy: false,
                loading: false,
                type: '',
                form: new Form({
                })
            }
        },

        async mounted() {
            this.loading = false;
        },

        computed: {
            submitUrl() {
                return ''; 
            },
        },

        methods: {
            submit() {
                let form = this.$refs.creditCardForm.form;
                if (this.type === 'bank_account') {
                    form = this.$refs.bankAccountForm.form;
                }

                this.busy = true;
                form.post(`/account-setup/clients/${this.token}/step3`)
                    .then( ({ data }) => {
                        this.$emit('updated', data.data);
                    })
                    .catch(e => {
                    })
                    .finally(() => {
                        this.busy = false;
                    });
            }
        }
    }
</script>
