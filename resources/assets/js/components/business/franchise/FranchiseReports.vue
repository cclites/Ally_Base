<template>
    <b-card>
        <b-row>
            <b-table striped hover :items="items" :fields="fields">
                <template slot="report_name"scope="data">
                    <a :href="'#'">
                        {{ data.item.report_name }}
                    </a>
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
                fields: [ 'report_name', 'what_this_reports_show'],
                items: [
                    { report_name: 'Franchise Payments Suumay', what_this_reports_show: 'Suumary of payments per franchise'},
                    { report_name: 'Royalties Suumary', what_this_reports_show: 'Royalties paid/owed'},
                    { report_name: 'Payments Dispursed', what_this_reports_show: 'Payments Dispursed to franchises'},
                    { report_name: 'Payments Held', what_this_reports_show: 'Payments held'},
                    { report_name: 'Franchise Health', what_this_reports_show: 'Visit suumary and growth over period of time'}
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