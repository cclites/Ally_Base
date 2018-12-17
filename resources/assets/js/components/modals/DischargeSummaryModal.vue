<template>
    <b-modal v-model="visible" :title="`${client.name} - Discharge Summary`" size="lg">
        <b-row>
            <b-col><b>Client information</b></b-col>
        </b-row>
        <b-row class="mb-1">
            <b-col md="6">
                Name: {{ client.name }}
            </b-col>
            <b-col md="6">
                Start of Care: {{ formatDate(client.service_start_date) }}
            </b-col>
        </b-row>
        <b-row>
            <b-col>
                Address: {{ address }}
            </b-col>
        </b-row>
        <b-row class="mt-2">
            <b-col><b>Physician Information</b></b-col>
        </b-row>
        <b-row class="mb-1">
            <b-col md="6">Name: {{ client.dr_first_name }} {{ client.dr_last_name }}</b-col>
            <b-col md="6">Discharge Date: {{ formatDate(client.inactive_at)}}</b-col>
        </b-row>
        <b-row>
            <b-col md="6">Phone: {{ client.dr_phone }}</b-col>
            <b-col md="6">Last Service Date: {{ formatDate(client.last_service_date.date) }} </b-col>
        </b-row>
        <b-row class="mt-2">
            <b-col><b>Primary Diagnosis</b></b-col>
        </b-row>
        <b-row>
            <b-col>
                {{ client.diagnosis }}
            </b-col>
        </b-row>
        <b-row class="mt-2">
            <b-col><b>Discharge Reason</b></b-col>
        </b-row>
        <b-row>
            <b-col>
                {{ client.discharge_reason }}
            </b-col>
        </b-row>
        <b-row class="mt-2">
            <b-col><b>Evaluation of Goals</b></b-col>
        </b-row>
        <b-row>
            <b-col>
                {{ client.discharge_goals_eval }}
            </b-col>
        </b-row>
        <b-row class="mt-2">
            <b-col><b>Condition of Client at Discharge</b></b-col>
        </b-row>
        <b-row>
            <b-col>
                {{ client.discharge_condition }}
            </b-col>
        </b-row>
        <b-row class="mt-2">
            <b-col><b>Disposition of Client</b></b-col>
        </b-row>
        <b-row>
            <b-col>
                {{ client.discharge_disposition }}
            </b-col>
        </b-row>
    </b-modal>
</template>

<script>
    import FormatsStrings from '../../mixins/FormatsStrings'
    import FormatsDates from '../../mixins/FormatsDates'

    export default {
        props: {
            client: {
                type: Object,
                required: true
            }
        },

        mixins: [FormatsStrings, FormatsDates],

        components: {},

        data () {
            return {
                visible: false
            }
        },

        created () {
        },

        mounted () {
        },

        computed: {
            address () {
                return this.addressFormat(this.client.addresses[0])
            }
        },

        methods: {
            show () {
                this.visible = true
            },

            hide () {
                this.visible = false
            }
        }
    }
</script>

<style lang="scss">
</style>
