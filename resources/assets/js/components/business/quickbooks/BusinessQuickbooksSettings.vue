<template>
    <b-card no-body>
        <b-tabs pills card>
            <b-tab title="Setup" active>
                <quickbooks-setup></quickbooks-setup>
            </b-tab>

            <b-tab title="General Mapping">
                <quickbooks-general-mapping></quickbooks-general-mapping>
            </b-tab>

            <b-tab title="Rate Mapping">
                <quickbooks-rate-mapping></quickbooks-rate-mapping>
            </b-tab>

            <b-tab title="Client Mapping">
                <quickbooks-client-mapping :clients="clients"></quickbooks-client-mapping>
            </b-tab>

            <b-tab title="Caregiver Mapping">
                <quickbooks-caregiver-mapping :caregivers="caregivers"></quickbooks-caregiver-mapping>
            </b-tab>
        </b-tabs>
    </b-card>
</template>

<script>

    export default {
        props: {
            clients: Array,
            caregivers: Array
        },

        data() {
            return {

            }
        },

        methods: {

        },

        mounted() {

        },

    }
</script>

<style>

</style>
