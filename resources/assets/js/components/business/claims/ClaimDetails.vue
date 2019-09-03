<template>
    <b-card>
        <div class="claim-info">
            <h1>Claim #{{ claim.name }}</h1>
            <b-row>
                <b-col lg="6">
                    <b-form-group label="Client" label-for="client" class="bold">
                        <label><a :href="`/business/clients/${claim.client_id}`" target="_blank">{{ claim.client.name }}</a></label>
                    </b-form-group>
                </b-col>
                <b-col lg="6">
                    <b-form-group label="Related Client Invoice" label-for="client_invoice_id" class="bold">
                        <label><a :href="`/business/client/invoices/${claim.client_invoice_id}`" target="_blank">#{{ claim.client_invoice.name }}</a></label>
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
                    <b-form-group label="Amount Due" label-for="amount_due" class="bold">
                        <label>{{ moneyFormat(claim.amount_due) }}</label>
                    </b-form-group>
                </b-col>
            </b-row>
        </div>
        <hr />
        <div class="edit-claim-form">
            <b-row>
                <b-col lg="6">
                    <b-form-group label="Client First Name" label-for="client_first_name" label-class="required">
                        <b-form-input
                            v-model="form.client_first_name"
                            id="client_first_name"
                            name="client_first_name"
                            type="text"
                            :disabled="form.busy"
                        ></b-form-input>
                        <input-help :form="form" field="client_first_name" text=""></input-help>
                    </b-form-group>
                </b-col>
                <b-col lg="6">
                    <b-form-group label="Client Last Name" label-for="client_last_name" label-class="required">
                        <b-form-input
                            v-model="form.client_last_name"
                            id="client_last_name"
                            name="client_last_name"
                            type="text"
                            :disabled="form.busy"
                        ></b-form-input>
                        <input-help :form="form" field="client_last_name" text=""></input-help>
                    </b-form-group>
                </b-col>
            </b-row>
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
                    <b-form-group label="Client Medicaid ID" label-for="client_medicaid_id">
                        <b-form-input
                            v-model="form.client_medicaid_id"
                            id="client_medicaid_id"
                            name="client_medicaid_id"
                            type="text"
                            :disabled="form.busy"
                        ></b-form-input>
                        <input-help :form="form" field="client_medicaid_id" text=""></input-help>
                    </b-form-group>
                </b-col>
            </b-row>
            <b-row>
                <b-col lg="6">
                    <b-form-group label="Client Date of Birth" label-for="client_dob">
                        <mask-input v-model="form.client_dob" id="client_dob" type="date" :disabled="form.busy"></mask-input>
                        <input-help :form="form" field="date_of_birth" text="Enter their date of birth. Ex: MM/DD/YYYY"></input-help>
                    </b-form-group>
                </b-col>
                <b-col lg="6">
                    <b-form-group label="Client Medicaid Diagnosis Codes" label-for="client_medicaid_diagnosis_codes">
                        <b-form-input
                            v-model="form.client_medicaid_diagnosis_codes"
                            id="client_medicaid_diagnosis_codes"
                            name="client_medicaid_diagnosis_codes"
                            type="text"
                            :disabled="form.busy"
                        ></b-form-input>
                        <input-help :form="form" field="client_medicaid_diagnosis_codes" text=""></input-help>
                    </b-form-group>
                </b-col>
            </b-row>
            <b-row>
                <b-col lg="6">
                    <b-form-group label="Plan Code" label-for="plan_code">
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
                <b-col lg="6">
                    <b-form-group label="Transmission Method" label-for="selectedTransmissionMethod">
                        <transmission-method-dropdown v-model="form.transmission_method" :disabled="form.busy" />
                    </b-form-group>
                </b-col>
            </b-row>
            <b-row>
                <b-col class="d-flex">
                    <b-btn class="ml-auto" variant="success" @click="save()" :disabled="form.busy">Save Changes</b-btn>
                </b-col>
            </b-row>
        </div>

        <hr />
        <h2>Claimable Items</h2>
        <claim-invoice-items-table />
    </b-card>
</template>

<script>
    import FormatsDates from "../../../mixins/FormatsDates";
    import FormatsStrings from "../../../mixins/FormatsStrings";
    import FormatsNumbers from "../../../mixins/FormatsNumbers";
    import TransmissionMethodDropdown from "./TransmissionMethodDropdown";
    import ClaimInvoiceItemsTable from "./ClaimInvoiceItemsTable";
    import { mapGetters } from 'vuex';

    export default {
        components: {ClaimInvoiceItemsTable, TransmissionMethodDropdown},
        mixins: [FormatsDates, FormatsStrings, FormatsNumbers],

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
                    client_first_name: data.client_first_name || '',
                    client_last_name: data.client_last_name || '',
                    payer_code: data.payer_code || '',
                    client_medicaid_id: data.client_medicaid_id || '',
                    client_dob: (data.client_dob) ? this.formatDate(data.client_dob) : null,
                    client_medicaid_diagnosis_codes: data.client_medicaid_diagnosis_codes || '',
                    plan_code: data.plan_code || '',
                    transmission_method: data.transmission_method || '',
                });
            },
        },

        created() {
            this.$store.commit('claims/setClaim', this.originalClaim);
            this.initForm(this.claim);
        },

        mounted() {
        }
    }
</script>
