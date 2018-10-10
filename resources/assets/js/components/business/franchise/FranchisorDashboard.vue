<template>
    <b-card>
        <b-row class="border-bottom mb-5">
            <b-col md="12"  class="mb-2">
                <h3 class="popover-header">Franchisor Account</h3>
            </b-col>
            <b-col md="4" class="text-center">
                <b-alert variant="success" show>Total Offices: 100</b-alert>
            </b-col>
            <b-col md="4">
                <b-alert variant="success" show>Total Visits: 250</b-alert>
            </b-col>
            <b-col md="4">
                <b-alert variant="success" show>Total Payments Last Week: $10</b-alert>
            </b-col>
        </b-row>
        <b-row>
            <b-col md="12" class="mb-2">
                <h3 class="">Latest Payments</h3>
            </b-col>
            <b-table striped hover :items="items" :fields="fields">
                <template slot="action" scope="row">
                    <b-button size="sm" class="mr-2">
                        View Details
                    </b-button>
                </template>
            </b-table>
        </b-row>
    </b-card>
</template>

<script>
    export default {
        props: {

        },

        data() {
            return {
                fields: [ 'date', 'office', 'amount', 'action'],
                items: [
                    { date: '10/1/18', office: 'Griswold Plano TX', amount: '$14,590.39'},
                    { date: '10/1/18', office: 'Griswold Plano TX', amount: '$14,590.39'},
                    { date: '10/1/18', office: 'Griswold Plano TX', amount: '$14,590.39'},
                    { date: '10/1/18', office: 'Griswold Plano TX', amount: '$14,590.39'}
                ]
            }
        },

        mounted() {

        },

        computed: {

        },

        methods: {

        }
    }
</script>

<style scoped>
    .border-bottom {
        border-bottom: 2px solid #bdb8b8;
    }
</style>