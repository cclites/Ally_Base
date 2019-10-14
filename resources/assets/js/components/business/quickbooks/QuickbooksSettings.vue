<template>
    <div>
        <b-row>
            <b-col md="12">
                <business-location-form-group
                    v-model="business_id"
                    label="Choose Office Location"
                />
            </b-col>
        </b-row>
        <b-card no-body>
            <loading-card v-if="loading" text="Loading..." class="mt-4"/>

            <b-tabs v-else pills card>
                <b-tab title="Connect" active>
                    <business-quickbooks-connect-settings
                        :businessId="business_id"
                        :connection="connection"
                    />
                </b-tab>
                <b-tab title="General Settings" :disabled="! connection.is_authenticated">
                    <business-quickbooks-general-settings
                        :businessId="business_id"
                        :connection="connection"
                    />
                </b-tab>
                <b-tab title="Client Mapping" :disabled="! connection.is_authenticated">
                    <business-quickbooks-client-map-settings
                        :clients="clients"
                        :businessId="business_id"
                        :connection="connection"
                    />
                </b-tab>
            </b-tabs>
        </b-card>
    </div>
</template>

<script>
    import BusinessLocationFormGroup from '../../../components/business/BusinessLocationFormGroup';

    export default {
        components: { BusinessLocationFormGroup },

        props: {
        },

        data() {
            return {
                loading: false,
                business_id: '',
                clients: [],
                connection: {},
            }
        },

        methods: {
            fetchConnection() {
                axios.get(`/business/quickbooks?business_id=${this.business_id}&json=1`)
                    .then( ({ data }) => {
                        this.connection = data.connection;
                        this.clients = data.clients;
                    })
                    .catch(() => {})
                    .finally(() => {
                        this.loading = false;
                    })
            },
        },

        watch: {
            business_id(newValue, oldValue) {
                if (newValue) {
                    this.loading = true;
                    this.fetchConnection();
                }
            },
        },

        mounted() {
            this.loading = true;
            if (this.business_id) {
                this.fetchConnection();
            }
        }
    }
</script>
