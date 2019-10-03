<template>
    <div>
        <b-alert variant="info" show>
            In order to transmit this claim for this invoice (<a :href="`/business/client/invoices/${invoice.id}`" target="_blank">{{ invoice.name}}</a>), we need some additional data.  Once you enter this information we will not ask again - it will be saved for future transmissions.
        </b-alert>
        <b-row>
            <b-col md="6" v-if="hasGroup(['business'])" class="mb-4">
                <h4>Business Settings</h4>
                <b-form-group v-if="hasField('business_ein')" label="Business EIN" label-class="required">
                    <b-form-input
                        name="business_ein"
                        type="text"
                        v-model="form.business_ein"
                        max="255"
                        :disabled="form.busy"
                    />
                    <input-help :form="form" field="business_ein" text="" />
                </b-form-group>
                <b-form-group v-if="hasField('business_zip')" label="Business Zipcode" label-class="required">
                    <b-form-input
                        name="business_zip"
                        type="text"
                        v-model="form.business_zip"
                        max="255"
                        :disabled="form.busy"
                    />
                    <input-help :form="form" field="business_zip" text="" />
                </b-form-group>
                <b-form-group v-if="hasField('business_medicaid_id')" label="Medicaid ID" label-class="required">
                    <b-form-input
                        name="business_medicaid_id"
                        type="text"
                        v-model="form.business_medicaid_id"
                        max="255"
                        :disabled="form.busy"
                    />
                    <input-help :form="form" field="business_medicaid_id" text="" />
                </b-form-group>
                <b-form-group v-if="hasField('business_medicaid_npi_number')" label="Medicaid NPI Number" label-class="required">
                    <b-form-input
                        name="business_medicaid_npi_number"
                        type="text"
                        v-model="form.business_medicaid_npi_number"
                        max="255"
                        :disabled="form.busy"
                    />
                    <input-help :form="form" field="business_medicaid_npi_number" text="" />
                </b-form-group>
                <b-form-group v-if="hasField('business_medicaid_npi_taxonomy')" label="Medicaid NPI Taxonomy" label-class="required">
                    <b-form-input
                        name="business_medicaid_npi_taxonomy"
                        type="text"
                        v-model="form.business_medicaid_npi_taxonomy"
                        max="255"
                        :disabled="form.busy"
                    />
                    <input-help :form="form" field="business_medicaid_npi_taxonomy" text="" />
                </b-form-group>
            </b-col>

            <b-col md="6" v-if="hasGroup(['client'])" class="mb-4">
                <h4>Client Settings (<a :href="`/business/clients/${invoice.client_id}`" target="_blank">{{ invoice.client.name }}</a>)</h4>
                <b-form-group v-if="hasField('client_date_of_birth')" label="Date of Birth" label-class="required">
                    <b-form-input
                        name="client_date_of_birth"
                        type="text"
                        v-model="form.client_date_of_birth"
                        max="255"
                        :disabled="form.busy"
                    />
                    <input-help :form="form" field="client_date_of_birth" text="" />
                </b-form-group>
                <b-form-group v-if="hasField('client_medicaid_id')" label="Medicaid ID" label-class="required">
                    <b-form-input
                        name="client_medicaid_id"
                        type="text"
                        v-model="form.client_medicaid_id"
                        max="255"
                        :disabled="form.busy"
                    />
                    <input-help :form="form" field="client_medicaid_id" text="" />
                </b-form-group>
                <b-form-group v-if="hasField('client_medicaid_payer_id')" :label="`MCO / Payer Identifier (<a href='${EDI_CODE_GUIDE_URL}' target='_blank'>Code Guides: HHA</a>) (<a href='${TELLUS_CODE_GUIDE_URL}' target='_blank'>Code Guides: Tellus</a>)`" label-class="required">
                    <b-form-input
                        name="client_medicaid_payer_id"
                        type="text"
                        v-model="form.client_medicaid_payer_id"
                        max="255"
                        :disabled="form.busy"
                    />
                    <input-help :form="form" field="client_medicaid_payer_id" text="For use only if you are submitting a private pay claim for reimbursement (not common)" />
                </b-form-group>
                <b-form-group v-if="hasField('client_medicaid_plan_id')" :label="`Plan Identifier (<a href='${TELLUS_CODE_GUIDE_URL}' target='_blank'>Code Guides: Tellus</a>)`" label-class="required">
                    <b-form-input
                        name="client_medicaid_plan_id"
                        type="text"
                        v-model="form.client_medicaid_plan_id"
                        max="255"
                        :disabled="form.busy"
                    />
                    <input-help :form="form" field="client_medicaid_plan_id" text="For use only if you are submitting a private pay claim for reimbursement (not common)" />
                </b-form-group>
                <b-form-group v-if="hasField('client_medicaid_diagnosis_codes')" label="Medicaid Diagnosis Codes" label-class="required">
                    <b-form-input
                        name="client_medicaid_diagnosis_codes"
                        type="text"
                        v-model="form.client_medicaid_diagnosis_codes"
                        max="255"
                        :disabled="form.busy"
                    />
                    <input-help :form="form" field="client_medicaid_diagnosis_codes" text="Note: Supports multiple using commas" />
                </b-form-group>
            </b-col>

            <b-col md="6" v-if="hasGroup(['payer'])" class="mb-4">
                <h4>Payer Settings ({{ invoice.clientPayer.payer.name }})</h4>
                <b-form-group v-if="hasField('payer_payer_code')" :label="`MCO / Payer Identifier (<a href='${EDI_CODE_GUIDE_URL}' target='_blank'>Code Guides: HHA</a>) (<a href='${TELLUS_CODE_GUIDE_URL}' target='_blank'>Code Guides: Tellus</a>)`" label-class="required">
                    <b-form-input
                        name="payer_payer_code"
                        type="text"
                        v-model="form.payer_payer_code"
                        max="255"
                        :disabled="form.busy"
                    />
                    <input-help :form="form" field="payer_payer_code" text="" />
                </b-form-group>
                <b-form-group v-if="hasField('payer_plan_code')" :label="`Plan Identifier (<a href='${TELLUS_CODE_GUIDE_URL}' target='_blank'>Code Guides: Tellus</a>)`" label-class="required">
                    <b-form-input
                        name="payer_plan_code"
                        type="text"
                        v-model="form.payer_plan_code"
                        max="255"
                        :disabled="form.busy"
                    />
                    <input-help :form="form" field="payer_plan_code" text="" />
                </b-form-group>
            </b-col>

            <b-col md="6" v-if="hasGroup(['credentials'])" class="mb-4">
                <h4>Transmission Credentials</h4>
                <b-form-group v-if="hasField('credentials_hha_username')" label="HHA Username" label-class="required">
                    <b-form-input
                        name="credentials_hha_username"
                        type="text"
                        v-model="form.credentials_hha_username"
                        max="255"
                        :disabled="form.busy"
                    />
                    <input-help :form="form" field="credentials_hha_username" text="" />
                </b-form-group>
                <b-form-group v-if="hasField('credentials_hha_password')" label="HHA Password" label-class="required">
                    <b-form-input
                        name="credentials_hha_password"
                        type="text"
                        v-model="form.credentials_hha_password"
                        max="255"
                        :disabled="form.busy"
                    />
                    <input-help :form="form" field="credentials_hha_password" text="" />
                </b-form-group>
                <b-form-group v-if="hasField('credentials_tellus_username')" label="Tellus Username" label-class="required">
                    <b-form-input
                        name="credentials_tellus_username"
                        type="text"
                        v-model="form.credentials_tellus_username"
                        max="255"
                        :disabled="form.busy"
                    />
                    <input-help :form="form" field="credentials_tellus_username" text="" />
                </b-form-group>
                <b-form-group v-if="hasField('credentials_tellus_password')" label="Tellus Password" label-class="required">
                    <b-form-input
                        name="credentials_tellus_password"
                        type="text"
                        v-model="form.credentials_tellus_password"
                        max="255"
                        :disabled="form.busy"
                    />
                    <input-help :form="form" field="credentials_tellus_password" text="" />
                </b-form-group>
            </b-col>
        </b-row>
    </div>
</template>

<script>
    import Constants from '../../mixins/Constants';
    export default {
        mixins: [Constants],
        props: {
            invoice: {
                type: Object,
                default: () => {
                },
            },
        },

        data() {
            return {
                form: new Form({}),
            };
        },

        computed: {
            busy() {
                return this.form.busy;
            },
        },

        methods: {
            hasGroup(group) {
                for (let field of Object.keys(this.form.originalData)) {
                    if (field.startsWith(group + '_')) {
                        return true;
                    }
                }
                return false;
            },

            hasField(field) {
                return this.form.originalData.hasOwnProperty(field)
            },

            createForm(errors) {
                let fields = {};

                if (errors.hasOwnProperty('business')) {
                    for (let field of errors.business) {
                        fields['business_' + field] = '';
                    }
                }

                if (errors.hasOwnProperty('client')) {
                    for (let field of errors.client) {
                        fields['client_' + field] = '';
                    }
                }

                if (errors.hasOwnProperty('payer')) {
                    for (let field of errors.payer) {
                        fields['payer_' + field] = '';
                    }
                }

                if (errors.hasOwnProperty('credentials')) {
                    for (let field of errors.credentials) {
                        fields['credentials_' + field] = '';
                    }
                }

                this.form = new Form(fields);
            },

            submit() {
                this.form.patch(`claims-ar/${this.invoice.id}/update-missing-fields`)
                    .then( ({ data }) => {
                        this.$emit('close', true);
                    })
                    .catch(e => {})
                    .finally(() => {});
            },
        },
    }
</script>
