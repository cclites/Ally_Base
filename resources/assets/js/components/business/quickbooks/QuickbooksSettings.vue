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
            <b-tabs pills card>
                <b-tab title="Connect" active>
                    <business-quickbooks-connect-setting
                        :businessId="business_id"
                        :authenticated="authenticated"
                        :company="company"
                    />
                </b-tab>
                <b-tab title="Client Mapping" :disabled="! authenticated">
                    <business-quickbooks-client-map-settings
                        :clients="clients"
                        :businessId="business_id"
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
            clients: {
                type: Array,
                default: [],
            },
            caregivers: {
                type: Array,
                default: [],
            },
            authenticated: {
                type: Boolean,
                default: false,
            },
            company: {
                type: String,
                default: '',
            },
        },

        data() {
            return {
                business_id: '',
            }
        },
    }
</script>
