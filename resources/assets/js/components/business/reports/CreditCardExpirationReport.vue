<template>
    <b-row>
        <b-col>
            <b-card title="Credit Card Expiration">
                <b-table :items="cards"
                         :fields="fields"></b-table>
            </b-card>
        </b-col>
    </b-row>
</template>

<script>
    export default {
        props: ['cards'],

        mixins: [],

        data() {
            return{
                fields: [
                    {
                        key: 'user',
                        formatter: (value) => { return value.name; }
                    },
                    'name_on_card',
                    'type',
                    'expiration_month',
                    'expiration_year',
                    {
                        key: 'expires_in',
                        label: 'Expires',
                        formatter: (value) => { return value + ' expiration'; }
                    }
                ]
            }
        },

        created() {

        },

        mounted() {

        },

        methods: {

        },

        computed: {

        }
    }
</script>