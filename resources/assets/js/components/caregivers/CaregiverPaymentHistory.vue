<template>
    <div>
        <b-row>
            <b-col lg="4">
                <dashboard-metric variant="info" value="$0" text="This Month" />
            </b-col>
            <b-col lg="4">
                <dashboard-metric variant="primary" value="$0" text="Scheduled" />
            </b-col>
            <b-col lg="4">
                <dashboard-metric variant="success" value="$0" text="Year to Date" />
            </b-col>
        </b-row>
        <b-card>
            <b-table :items="payments" :fields="fields">
                <template slot="created_at" scope="data">
                    {{ formatDate(data.item.created_at) }}
                </template>
                <template slot="week" scope="data">
                    {{ formatDate(data.item.week.start) }} - {{ formatDate(data.item.week.end) }}
                </template>
                <template slot="caregiver_allotment" scope="data">
                    &dollar;{{ data.item.caregiver_allotment }}
                </template>
                <template slot="actions" scope="data">
                    <a :href="'/reports/payment-history/' + data.item.id" class="btn btn-secondary">View Details</a>
                </template>
            </b-table>
        </b-card>
    </div>
</template>

<style lang="scss">
</style>

<script>
    import DashboardMetric from '../DashboardMetric';
    import FormatsDates from '../../mixins/FormatsDates';
    export default {
        props: ['caregiver', 'payments'],

        components: {
            DashboardMetric
        },

        mixins: [FormatsDates],

        data() {
            return {
                fields: [
                    { key: 'created_at', label: 'Paid' },
                    { key: 'week', label: 'For Care Week'},
                    'caregiver_allotment',
                    'actions'
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