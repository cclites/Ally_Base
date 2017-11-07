<template>
    <b-card
        :header="title"
        header-text-variant="white"
        header-bg-variant="info"
        >
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
                <credit-card-form v-if="type == 'credit_card'" :source="source" :card="existing_card" :client="client" />
                <bank-account-form v-if="type == 'bank_account'" :source="source" :account="existing_account" :submit-url="'/business/clients/' + client.id + '/payment/' + source" />
            </b-col>
        </b-row>
    </b-card>
</template>

<script>
    export default {
        props: {
            'title': {},
            'method': {},
            'source': {},
            'client': {},
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
                existing_card: {},
                existing_account: {},
            }
        },

        mounted() {
            if (this.method) {
                if (this.method.account_type) {
                    console.log('BANK ACCOUNT FOUND');
                    this.type = 'bank_account';
                    this.existing_account = this.method;
                }
                else if (this.method.expiration_year) {
                    console.log('CREDIT CARD FOUND');
                    this.type = 'credit_card';
                    this.existing_card = this.method;
                }
            }
        },

        methods: {

        },
    }
</script>
