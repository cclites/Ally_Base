<template>
    <b-row>
        <b-col md="12">
            <div v-if="connection.is_authenticated" class="text-center">
                <div v-if="connection.is_desktop">
                    <b-alert show variant="info" class="mb-4">You can connect to your Quickbooks Desktop installation using our <a href="#">Ally Quickbooks Desktop Receiver Tool</a>.<br/>You can follow <a href="#">the help guide</a> if you need assistance getting set up.</b-alert>
                    <div class="mt-4 mb-4">
                        <div><strong>Company Name:</strong> {{ connection.company_name }}</div>
                        <div><strong>Your API Key:</strong> {{ connection.desktop_api_key }}</div>
                        <div><strong>Last Connection:</strong> {{ connection.last_connected_at ? formatDateFromUTC(connection.last_connected_at) : 'Never' }}</div>
                    </div>
                    <b-btn @click="disconnect()" variant="danger">Disable Quickbooks Desktop Connections</b-btn>
                </div>
                <div v-else>
                    <b-alert show variant="success" class="mb-4">Your account is connected to the Quickbooks API.</b-alert>
                    <div class="mt-4 mb-4">
                        <strong>Company Name:</strong> {{ connection.company_name }}
                    </div>
                    <b-btn @click="disconnect()" variant="danger">Disconnect Your Quickbooks Online Account</b-btn>
                </div>
            </div>
            <div v-else class="text-center">
                <b-alert show variant="warning" class="mb-4">You must authorize our application to connect to your Quickbooks account.</b-alert>
                <b-btn :href="`/business/quickbooks/${businessId}/connect`" variant="info" size="lg">Connect Your Quickbooks Online Account</b-btn>
                <div class="my-4"><strong>- or -</strong></div>
                <b-btn :href="`/business/quickbooks/${businessId}/enable-desktop`" variant="info" size="lg">Connect to Quickbooks Desktop</b-btn>
            </div>
        </b-col>
    </b-row>
</template>

<script>
    import FormatsDates from "../../../mixins/FormatsDates";
    export default {
        mixins: [FormatsDates],
        props: {
            connection: {
                type: [Array, Object],
                default: () => { return {}; },
            },
            businessId: {
                type: [String, Number],
                default: '',
            },
        },

        data() {
            return {
            }
        },

        computed: {
        },

        methods: {
            disconnect() {
                let form = new Form({});
                form.post(`/business/quickbooks/${this.businessId}/disconnect`)
                    .then((response) => {
                    })
                    .catch(() => {
                    })
            },
        },

        mounted() {
        },
    }
</script>
