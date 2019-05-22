<template>
    <b-row>
        <b-col md="12">
            <div v-if="connection.is_authenticated" class="text-center">
                <b-alert show variant="success" class="mb-4">Your account is connected to the Quickbooks API.</b-alert>
                <div class="mt-4 mb-4">
                    <strong>Company Name:</strong> {{ connection.company_name }}
                </div>
                <b-btn @click="disconnect()" variant="danger">Disconnect your Quickbooks account</b-btn>
            </div>
            <div v-else class="text-center">
                <b-alert show variant="warning" class="mb-4">You must authorize our application to connect to your Quickbooks account.</b-alert>
                <b-btn :href="`/business/quickbooks/${businessId}/connect`" variant="info" size="lg">Connect your Quickbooks account</b-btn>
            </div>
        </b-col>
    </b-row>
</template>

<script>
    export default {
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
