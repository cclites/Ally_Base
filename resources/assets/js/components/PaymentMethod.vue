<template>
    <b-card
        :header="title"
        header-text-variant="white"
        header-bg-variant="info"
        >
        <form @submit.prevent="saveAddress()" @keydown="form.clearError($event.target.name)">
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
                    <credit-card-form v-if="type == 'credit_card'" :form="form" />
                </b-col>
            </b-row>
        </form>
    </b-card>
</template>

<script>
    export default {
        props: {
            'title': null,
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
                type: 'credit_card',
                form: new Form({

                }),
            }
        },

        mounted() {
            console.log('Component mounted.')
        },

        methods: {

        }
    }
</script>
