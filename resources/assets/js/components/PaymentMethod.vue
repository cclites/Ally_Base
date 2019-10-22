<template>
    <b-card
            :header="title"
            header-text-variant="white"
            header-bg-variant="info"
    >
        <b-row v-if="restricted">
            <b-col lg="12" class="text-center">
                Please Contact Ally
            </b-col>
        </b-row>
        <b-row>
            <b-col class="text-right hidden-xs-down">
                <b-btn @click="deleteMethod()" :disabled="authInactive" v-if="method.type">Delete <i class="fa fa-times"></i></b-btn>
            </b-col>
        </b-row>
        <b-row v-if="!restricted">
            <b-col lg="12">
                <b-form-group label="Payment Type" label-for="type">
                    <b-form-select
                            id="type"
                            name="type"
                            v-model="type"
                            :options="types"
                            :disabled="authInactive || (type === 'trust' && !isAdmin)"
                    >
                    </b-form-select>
                </b-form-group>
                <credit-card-form v-if="type == 'credit_card'" 
                    :source="source" 
                    :card="existing_card" 
                    :client="client" 
                    :submit-url="submitUrl" 
                    :key="existing_card.id" 
                    :readonly="authInactive" 
                />
                <bank-account-form v-if="type == 'bank_account'" 
                    :source="source" 
                    :account="existing_account" 
                    :submit-url="submitUrl" 
                    :key="existing_account.id" 
                    :readonly="authInactive" 
                />
                <payment-method-provider v-if="business == true && type == 'provider'" 
                    :submit-url="submitUrl" 
                    :readonly="authInactive"
                />
                <payment-method-trust v-if="type == 'trust'"
                                         :submit-url="submitUrl"
                                         :readonly="!isAdmin"
                />
                <span class="hidden-sm-up">
                    <b-btn @click="deleteMethod()" :disabled="authInactive">Delete This Payment Method</b-btn>
                </span>
                <small class="form-text text-muted">
                    {{ typeMessage }}
                </small>
            </b-col>
        </b-row>
        <b-row v-show="hasMetrics" class="mt-3">
            <b-col>First Charge: {{ firstCharge }}</b-col>
            <b-col>Last Charge: {{ lastCharge }}</b-col>
            <b-col>Successful Charges: {{ chargeCount }}</b-col>
        </b-row>
    </b-card>
</template>

<script>
    import FormatsDates from "../mixins/FormatsDates";
    import AuthUser from "../mixins/AuthUser";
    import PaymentMethodTrust from "./PaymentMethodTrust";

    export default {
        components: {PaymentMethodTrust},
        mixins: [FormatsDates, AuthUser],

        props: {
            'role': String,
            'title': {},
            'method': {},
            'source': {},
            'client': {},
            'paymentTypeMessage': {
                default() {
                    return '';
                }
            },
            'business': null
        },

        data() {
            return {
                allTypes: [
                    {
                        'value': 'credit_card',
                        'text': 'Credit Card'
                    },
                    {
                        'value': 'bank_account',
                        'text': 'Bank Account'
                    },
                    {
                        'value': 'provider',
                        'text': 'Provider Payment Account',
                        'hidden': () => !this.business,
                    },
                    {
                        'value': 'trust',
                        'text': 'Trust Account',
                        'hidden': () => !this.isAdmin && this.type !== "trust",
                    }
                ],
                type: null,
                submitted: false,
                existing_card: {
                    id: 0,
                },
                existing_account: {
                    id: 0,
                },
                typeMessage: this.paymentTypeMessage
            }
        },

        mounted() {
            if (this.method) {
                if (this.method.account_type) {
                    this.type = 'bank_account';
                    this.existing_account = this.method;
                } else if (this.method.expiration_year) {
                    this.type = 'credit_card';
                    this.existing_card = this.method;
                } else if (this.method.payment_account_id) {
                    this.type = 'provider';
                } else if (this.method.client_id) {
                    this.type = 'trust';
                }
            }
        },

        computed: {
            types() {
                return this.allTypes.filter(item => item.hidden === undefined || item.hidden() == false);
            },

            submitUrl() {
                switch (this.role) {
                    case 'client':
                        return '/profile/payment/' + this.source;
                    case 'office_user':
                    case 'admin':
                        return '/business/clients/' + this.client.id + '/payment/' + this.source;
                }
            },

            hasMetrics() {
                return this.role !== 'client'
                    && this.type !== 'provider'
                    && this.method.charge_metrics;
            },

            restricted() {
                return this.role == 'client' && this.type == 'provider';
            },

            firstCharge() {
                if (this.hasMetrics && this.method.charge_metrics.first_charge_date) {
                    return this.formatDateFromUTC(this.method.charge_metrics.first_charge_date);
                }
                return 'Never';
            },

            lastCharge() {
                if (this.hasMetrics && this.method.charge_metrics.last_charge_date) {
                    return this.formatDateFromUTC(this.method.charge_metrics.last_charge_date);
                }
                return 'Never';
            },

            chargeCount() {
                if (this.hasMetrics && this.method.charge_metrics.successful_charge_count) {
                    return this.method.charge_metrics.successful_charge_count;
                }
                return '0';
            },
        },

        methods: {
            deleteMethod() {
                if (confirm('Are you sure you want to delete this payment method?')) {
                    let form = new Form();
                    form.submit('delete', this.submitUrl)
                        .then(response => {
                            this.onUpdatePaymentMethod(response.data.data);
                            this.type = null;
                            this.existing_account = {id: -1};
                            this.existing_card = {id: -1};
                        })
                }
            },

            onUpdatePaymentMethod(msg) {
                if (typeof msg === 'string') {
                    this.typeMessage = msg;
                } else {
                    this.typeMessage = msg.payment_text;
                    this.$store.commit('setPaymentMethodDetail', msg);
                }
            }
        }
    }
</script>
