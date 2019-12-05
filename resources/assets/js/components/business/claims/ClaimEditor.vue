<template>
    <b-card>
        <div class="claim-info">
            <div class="d-flex mb-3">
                <h1>Claim #{{ claim.name }}</h1>
                <div class="ml-auto">
                    <div class="text-right mb-2">
                        <b-btn variant="info" :href="`/business/claims/${claim.id}/print`" target="_blank"><i class="fa fa-print mr-2" />Print</b-btn>
                    </div>
                    <strong>Last Modified:</strong>
                    {{ claim.modified_at ? formatDateTimeFromUTC(claim.modified_at) : 'Never' }}
                </div>
            </div>
            <b-row>
                <b-col lg="6">
                    <b-form-group label="Client" label-for="client" class="bold">
                        <label v-if="claim.client_name == ''">(Grouped)</label>
                        <label v-else><a :href="`/business/clients/${claim.client_id}`" target="_blank">{{ claim.client_name }}</a></label>
                    </b-form-group>
                </b-col>
                <b-col lg="6">
                    <b-form-group label="Related Client Invoice" label-for="client_invoice_id" class="bold">
                        <div v-for="invoice in claim.invoices" key="invoice.id">
                            <label>
                                <a :href="`/business/client/invoices/${invoice.id}`" target="_blank">
                                    #{{ invoice.name }}
                                </a>
                            </label>
                        </div>
                    </b-form-group>
                </b-col>
            </b-row>
            <b-row>
                <b-col lg="6">
                    <b-form-group label="Payer" label-for="payer_id" class="bold">
                        <label>{{ claim.payer.name }}</label>
                    </b-form-group>
                </b-col>
                <b-col lg="6">
                    <b-form-group label="Status" label-for="status" class="bold">
                        <label>{{ snakeToTitleCase(claim.status) }}</label>
                    </b-form-group>
                </b-col>
            </b-row>
            <b-row>
                <b-col lg="6">
                    <b-form-group label="Amount" label-for="amount" class="bold">
                        <label>{{ moneyFormat(claim.amount) }}</label>
                    </b-form-group>
                </b-col>
                <b-col lg="6">
                    <b-form-group label="Claim Type" label-for="claim_invoice_type" class="bold">
                        <label>{{ resolveOption(claim.type, claimInvoiceTypeOptions) }}</label>
                    </b-form-group>
                </b-col>
            </b-row>
            <b-row>
                <b-col lg="6">
                    <b-form-group label="Amount Due" label-for="amount_due" class="bold">
                        <label>{{ moneyFormat(claim.amount_due) }}</label>
                    </b-form-group>
                </b-col>
                <b-col lg="6">
                </b-col>
            </b-row>
        </div>
        <hr />
        <div class="edit-claim-form">
            <b-row>
                <b-col lg="6">
                    <b-form-group label="Payer Code" label-for="payer_code">
                        <b-form-input
                            v-model="form.payer_code"
                            id="payer_code"
                            name="payer_code"
                            type="text"
                            :disabled="form.busy"
                        ></b-form-input>
                        <input-help :form="form" field="payer_code" text=""></input-help>
                    </b-form-group>
                </b-col>
                <b-col lg="6">
                    <b-form-group label="Payer Plan Identifier" label-for="plan_code">
                        <b-form-input
                            v-model="form.plan_code"
                            id="plan_code"
                            name="plan_code"
                            type="text"
                            :disabled="form.busy"
                        ></b-form-input>
                        <input-help :form="form" field="plan_code" text=""></input-help>
                    </b-form-group>
                </b-col>
            </b-row>
            <b-row>
                <b-col lg="6">
                    <b-form-group label="Transmission Method" label-for="selectedTransmissionMethod">
                        <transmission-method-dropdown v-model="form.transmission_method" :disabled="form.busy" />
                    </b-form-group>
                </b-col>
                <b-col lg="6">
                </b-col>
            </b-row>
            <b-row>
                <b-col class="d-flex">
                    <b-btn class="ml-auto" variant="success" @click="save()" :disabled="form.busy">Update Claim Information</b-btn>
                </b-col>
            </b-row>
        </div>

        <hr />
        <h2>Claimable Items</h2>
        <b-alert :show="claim.has_expenses" variant="info">
            This claim has expense items attached.  Note: HHA and Tellus do not accept expenses.
        </b-alert>
        <claim-invoice-items-table />
    </b-card>
</template>

<script>
    import TransmissionMethodDropdown from "./TransmissionMethodDropdown";
    import ClaimInvoiceItemsTable from "./ClaimInvoiceItemsTable";
    import FormatsStrings from "../../../mixins/FormatsStrings";
    import FormatsNumbers from "../../../mixins/FormatsNumbers";
    import FormatsDates from "../../../mixins/FormatsDates";
    import { mapGetters } from 'vuex';
    import Constants from "../../../mixins/Constants";

    export default {
        components: {ClaimInvoiceItemsTable, TransmissionMethodDropdown},
        mixins: [FormatsDates, FormatsStrings, FormatsNumbers, Constants],

        props: {
            originalClaim: {
                type: Object,
                default: () => {
                    return {}
                },
                required: true,
            },
        },

        data() {
            return {
                form: new Form({}),
            };
        },

        computed: {
            ...mapGetters({
                claim: 'claims/claim',
            }),
        },

        methods: {
            save() {
                this.form.patch(`/business/claims/${this.claim.id}`)
                    .then( ({ data }) => {
                        this.$store.commit('claims/setClaim', data.data);
                    })
                    .catch(() => {});
            },

            initForm(data) {
                this.form = new Form({
                    // client_first_name: data.client_first_name || '',
                    // client_last_name: data.client_last_name || '',
                    payer_code: data.payer_code || '',
                    // client_medicaid_id: data.client_medicaid_id || '',
                    // client_dob: (data.client_dob) ? this.formatDate(data.client_dob) : null,
                    // client_medicaid_diagnosis_codes: data.client_medicaid_diagnosis_codes || '',
                    plan_code: data.plan_code || '',
                    transmission_method: data.transmission_method || '',
                });
            },
        },

        created() {
            this.$store.commit('claims/setClaim', this.originalClaim);
            this.initForm(this.claim);
        },

        async mounted() {
            await this.$store.dispatch('claims/fetchCaregiverList');
            await this.$store.dispatch('claims/fetchServiceList');
        },
    }
</script>
