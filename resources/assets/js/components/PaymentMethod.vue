<template>
    <b-card
            :header="title"
            header-text-variant="white"
            header-bg-variant="info"
    >
        <b-row>
            <b-col class="text-right hidden-xs-down">
                <b-btn @click="deleteMethod()">Delete <i class="fa fa-times"></i></b-btn>
            </b-col>
        </b-row>
        <b-row>
            <b-col lg="12">
                <b-form-group label="Payment Type" label-for="type">
                    <b-form-select
                            id="type"
                            name="type"
                            v-model="type"
                            :options="types"
                    >
                    </b-form-select>
                </b-form-group>
                <credit-card-form v-if="type == 'credit_card'" :source="source" :card="existing_card" :client="client" :key="existing_card.id" />
                <bank-account-form v-if="type == 'bank_account'" :source="source" :account="existing_account" :submit-url="submitUrl" :key="existing_account.id" />
                <payment-method-provider v-if="business == true && type == 'provider'" :submit-url="submitUrl"/>
                <span class="hidden-sm-up">
                    <b-btn @click="deleteMethod()">Delete This Payment Method</b-btn>
                </span>
                <small class="form-text text-muted">
                    {{ typeMessage }}
                </small>
            </b-col>
        </b-row>
    </b-card>
</template>

<script>
    export default {
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
                types: [
                    {
                        'value': 'credit_card',
                        'text': 'Credit Card'
                    },
                    {
                        'value': 'bank_account',
                        'text': 'Bank Account'
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
            if (this.business) {
                this.types.push({
                    'value': 'provider',
                    'text': 'Provider Payment Account',
                })
            }
            if (this.method) {
                if (this.method.account_type) {
                    this.type = 'bank_account';
                    this.existing_account = this.method;
                } else if (this.method.expiration_year) {
                    this.type = 'credit_card';
                    this.existing_card = this.method;
                } else if (this.method.payment_account_id) {
                    this.type = 'provider';
                }
            }
        },

        computed: {
            submitUrl() {
                switch (this.role) {
                    case 'client':
                        return '/profile/payment/' + this.source;
                    case 'office_user':
                        return '/business/clients/' + this.client.id + '/payment/' + this.source;
                }
            }
        },

        methods: {
            deleteMethod() {
                if (confirm('Are you sure you want to delete this payment method?')) {
                    let form = new Form();
                    form.submit('delete', this.submitUrl)
                        .then(response => {
                            this.type = null;
                            this.existing_account = {id: -1};
                            this.existing_card = {id: -1};
                        })
                }
            }
        }
    }
</script>
